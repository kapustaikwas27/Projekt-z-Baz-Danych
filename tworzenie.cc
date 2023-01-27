/* Końcowa obróbka danych, której wynikiem są polecenia INSERT do tabel impreza oraz impreza_typ. */

#include <iostream>
#include <sstream>
#include <vector>

enum Pole {
    DATA,
    ID,
    NAZWA,
    MIEJSCE,
    TYP,
    OPIS
};

int main() {
    std::string linia, id, data_pocz, data_kon, nazwa, url_imprezy, miejsce, url_miejsca, opis;
    std::vector<std::string> id_typ;
    Pole pole = DATA;
    int numer = 0;

    while (std::getline(std::cin, linia)) {
        if (linia == "Data") {
            if (id.size() > 0) {
                std::stringstream wartosci;
                std::cout << "INSERT INTO impreza(id, data_pocz, data_kon";
                wartosci << id << ",\n\'" << data_pocz << "\',\n\'" << data_kon << "\'";

                if (url_imprezy.size() > 0) {
                    std::cout << ", url_imprezy";
                    wartosci << ",\n\'" << url_imprezy << "\'";
                }

                std::cout << ", nazwa";
                wartosci << ",\n\'" << nazwa << "\'";

                if (url_miejsca.size() > 0) {
                    std::cout << ", url_miejsca";
                    wartosci << ",\n\'" << url_miejsca << "\'";
                }

                std::cout << ", miejsce";
                wartosci << ",\n\'" << miejsce << "\'";

                if (opis.size() > 0) {
                    std::cout << ", opis";
                    wartosci << ",\n\'" << opis << "\'";
                }

                std::cout << ") VALUES (\n" << wartosci.str() << "\n);\n";

                for (int i = 0; i < id_typ.size(); i++) {
                    std::cout << "INSERT INTO impreza_typ(id_imprezy, id_typu) VALUES (" << id << ", " << id_typ[i] << ");\n";
                }
            }

            id = data_pocz = data_kon = nazwa = url_imprezy = miejsce = url_miejsca = opis = "";
            id_typ.clear();
            pole = DATA;
            numer = 0;
        } else if (linia == "Id") {
            pole = ID;
            numer = 0;
        } else if (linia == "Nazwa") {
            pole = NAZWA;
            numer = 0;
        } else if (linia == "Miejsce") {
            if (numer == 2) {
                nazwa = url_imprezy;
                url_imprezy = "";
            }

            pole = MIEJSCE;
            numer = 0;
        } else if (linia == "Typ") {
            if (numer == 2) {
                miejsce = url_miejsca;
                url_miejsca = "";
            }

            pole = TYP;
            numer = 0;
        } else if (linia == "Opis") {
            pole = OPIS;
            numer = 0;
        } else if (pole == DATA && numer == 1) {
            data_pocz = linia;
        } else if (pole == DATA && numer == 2) {
            data_kon = linia;
        }  else if (pole == ID && numer == 1) {
            id = linia;
        } else if (pole == NAZWA && numer == 1) {
            url_imprezy = linia;
        } else if (pole == NAZWA && numer == 2) {
            nazwa = linia;
        }  else if (pole == MIEJSCE && numer == 1) {
            url_miejsca = linia;
        } else if (pole == MIEJSCE && numer == 2) {
            miejsce = linia;
        }  else if (pole == TYP) {
            id_typ.push_back(linia);
        } else if (pole == OPIS) {
            opis = linia;
        }

        numer++;
    }

    std::cout << "COMMIT;" << std::endl;

    return 0;
}
