# Zadanie
** wdrożenie w projekcie bibliotek GSAP + ScrollTrigger **
## założenia
1. strona będzie posiadać moduły oparte na scroll i animacjach:
- animacje po pojawieniu się elemntu w viewport
- zatrzymanie elementu blur i jego wyostrzanie wraz z postępem scroll
- zatrzymanie tła sekcji ale przesuwanie innych elementów
- animacja wewnątrz sekcji - również oparta o scroll (np. wsuwanie się elementu z lewej oparte o scroll)
2. kluczowa jest płynność animacji
## plan pracy
1. dodaj biblioteki do npm
2. zaimplementuj je w widoku (osobno od zwykłego js, nie budujemy jednego, dużego pliku js)
3. dodaj plik funkcyjny, definiujący animację
4. w widoku front-page.balde.php dodaj @includ test/testpage.blade.php
5. w testpage.blade.php wygeneruj zestaw sekcji i dodaj do nich animacje:
- sekcja 1 - animacja tła, następnie wsunięcie się tytułu 'Lorem ipsum' z lewej
- sekcja 2 - zatrzymanie tła i scroll galerii typu masonery (placeholdery, 16 obrazów)
- sekcja 3 - podzielona na dwa - wyostrzenie na scroll sekcji po  lewej, wsunięcie od prawej sekcji prawej (po zakończeniu wyostrzania)
- sekcja 4 - 4 boxy wsuwające się od dołu, na scroll podświetlenie każdego po kolei
- sekcja 5 - analogicznie jak sekcja 3 ale sekcja po prawej zmienia opcity, sekcja po lewej wjeżdża od lewej
- sekcja 6 - zatrzymanie tła i animacja (opacity + ruch) od dołu.
- stopka kończy akcje na scroll