#!/bin/bash

# aktualny rok
rok=$(date +%Y)

# pobranie kodu html strony z danymi
curl https://www.orienteering.waw.pl/pl/calendar/${rok} |

sed 's/</\n</g' |

# wydzielenie tabeli imprez
awk '/<table/, /table>/' |

# wydobycie stron internetowych
sed 's/<a href=\"/\n/' |

# usunięcie fragmentów kodu html
awk 'BEGIN { FS = "<[^>]*>" } { $1 = $1 } 1' |
sed 's/\">/\n/' |
sed 's/regionalne:/regionalne/' |
sed 's/krajowe:/krajowe/' |
sed 's/zagraniczne:/zagraniczne/' |
awk '!/\/pl\/calendar|\/pl\/node|\/pl\/entry/' |
awk 'BEGIN { FS = "&\.\*;" } { $1 = $1 } 1' |

# ujednolicenie odstępów między słowami w wierszach
awk 'BEGIN { OFS = " " } { $1 = $1 } 1' |

# usunięcie pustych lub niepotrzebnych linii
awk 'NF > 0 && $0 != ","' |
awk 'BEGIN { wiecej = 0 }; /Więcej/ { wiecej = 1 }; /Data/ { wiecej = 0 }; 1 - wiecej { print }' |

# numeracja imprez (zgodna z numeracją na stronie)
awk '{ if ($0 ~ /[0-9]+ \(Publiczny\)/) { print "Id\n" $1 } else { print } }' > dane1.txt

# ujednolicenie formatu daty
mianowniki=("styczeń" "luty" "marzec" "kwiecień" "maj" "czerwiec" "lipiec" "sierpień" "wrzesień" "październik" "listopad" "grudzień")
dopelniacze=("stycznia" "lutego" "marca" "kwietnia" "maja" "czerwca" "lipca" "sierpnia" "września" "października" "listopada" "grudnia")
numery=("01" "02" "03" "04" "05" "06" "07" "08" "09" "10" "11" "12")

for i in {0..11}
do
    cat dane1.txt |
    sed "s/\(^[0-9]\)\( - \)\([0-9]\)\( ${dopelniacze[$i]} \)\([0-9][0-9][0-9][0-9]\)/0\1-${numery[$i]}-\5|0\3-${numery[$i]}-\5/" |
    sed "s/\(^[0-9]\)\( - \)\([0-9][0-9]\)\( ${dopelniacze[$i]} \)\([0-9][0-9][0-9][0-9]\)/0\1-${numery[$i]}-\5|\3-${numery[$i]}-\5/" |
    sed "s/\(^[0-9][0-9]\)\( - \)\([0-9]\)\( ${dopelniacze[$i]} \)\([0-9][0-9][0-9][0-9]\)/\1-${numery[$i]}-\5|0\3-${numery[$i]}-\5/" |
    sed "s/\(^[0-9][0-9]\)\( - \)\([0-9][0-9]\)\( ${dopelniacze[$i]} \)\([0-9][0-9][0-9][0-9]\)/\1-${numery[$i]}-\5|\3-${numery[$i]}-\5/" |
    sed "s/\(^[0-9]\)\( ${dopelniacze[$i]} -\)/0\1-${numery[$i]}-${rok}/" |
    sed "s/\(^[0-9][0-9]\)\( ${dopelniacze[$i]} -\)/\1-${numery[$i]}-${rok}/" |
    sed "s/\(^[0-9]\)\( ${dopelniacze[$i]} \)/0\1-${numery[$i]}-/" |
    sed "s/\(^[0-9][0-9]\)\( ${dopelniacze[$i]} \)/\1-${numery[$i]}-/" |
    sed "s/${mianowniki[$i]} /01-${numery[$i]}-/" > dane1.txt
done

cat dane1.txt | tr '|' '\n' |
awk '{ if ($0 ~ /[0-9]{2}-[0-9]{2}-[0-9]{4}/) { print $1 } else { print } }' | # usunięcie informacji za datą w wierszu (dzień tygodnia)
sed 's/\([0-9][0-9]\)\(-\)\([0-9][0-9]\)\(-\)\([0-9][0-9][0-9][0-9]\)/\5\4\3\2\1/' > dane2.txt # odwrócenie dat, żeby były w postaci YYYY-MM-DD
cat dane2.txt | tr '\n' '|' |
sed 's/\(Data\)\(|\)\([0-9][0-9][0-9][0-9]\)\(-\)\([0-9][0-9]\)\(-\)\([0-9][0-9]\)\(|\)\(Id\)/\1\2\3\4\5\6\7|\3\4\5\6\7\8\9/g' > dane1.txt # duplikacja pojedynczych dat
cat dane1.txt | tr '|' '\n' > dane2.txt

# wydzielenie typów imprez do pliku typy.txt
awk 'BEGIN { typ = 0; pierwszy = 0 } \
    { pierwszy = 0 } \
    /Typ/ { typ = 1; pierwszy = 1 } \
    /Opis/ { typ = 0 } \
    1 - pierwszy && typ == 1 { a[$1] } \
    END { for (w in a) print w }' dane2.txt > typy.txt

# zamiana nazw typów na id typów (numer linii w typy.txt) w pliku z danymi
awk 'NR == FNR { a[$1] = NR; next } \
    { if (NF == 1 && $1 in a) { print a[$1] } \
      else { print } \
    }' typy.txt dane2.txt > dane1.txt

# generowanie PL/SQL do dodania typów do tabeli typ
awk -v q="'" '{ print "INSERT INTO typ(id, nazwa) VALUES (" NR ", " q $0 q ");" }' typy.txt > inserty_typ.sql
echo "COMMIT;" >> inserty_typ.sql

# generowanie PL/SQL do dodania imprez do tabeli impreza oraz typów imprez do tabeli impreza_typ
echo "Data" >> dane1.txt
make
./tworzenie < dane1.txt > inserty_impreza_impreza_typ.sql

# sprzątanie
rm dane1.txt
rm dane2.txt
rm typy.txt
