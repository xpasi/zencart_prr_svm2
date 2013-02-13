prr_svm2 - Suomen verkkomaksut moduli
===============

prr_svm2 v1.0
---------------

Suomen verkkomaksut maksumoduli ZenCart verkkokauppaan. Käyttää REST API:a
Sisältää myös sivulaatikon, joka generoi kauppiastunnisteen perusteella käytössä olevat maksutapa ikonit näkyville (virallinen Suomen verkkomaksut "banneri").


Asennus
------------

1. Kopioi includes hakemisto kauppaan sellaisenaan. Asennus ei ylikirjoita tiedostoja
   - Kopioi prr_svm_handler.php tiedosto kaupan juureen (eli sinne missä index.php sijaitsee)
2. Asenna moduli normaalisti ZenCartin ylläpidossa.
   - Oletusasetukset mahdollistavat modulin testauksen.
   - Aseta tilauksen tilat sopiviksi.
     * Ensimmäinen tila on se johon tilaus päätyy kun asiakas palaa SVM:n palvelusta verkkokauppaan.
     * Toinen tila määrittää mihin tilaan tilaus laitetaan kun SVM:n palvelin on lähettänyt maksuvahvistuksen verkkokauppaan.
   - Aseta ALV kanta jota käytetään "käsittelykuluihin".
     Käsittelykuluilla tarkoitetaan kaikkia ot_total (eli loppusummaan vaikuttavia) moduleita.
     ZenCart ei tallenna tätä tietoa tilausta luodessaan eikä sitä voida päätellä luotettavasti jälkikäteen.

Loki
------------

Moduli tallentaa logs/ (tai cache/ jos logs hakemistoa ei ole) kaikki virheelliset maksuvahvistus yritykset. Tulivat ne sitten Suomen verkkomaksuilta tai joltain muulta taholta (huijaus yritys).

Tuki
------------
Tukea ja modulin kustomointia voi tarvittaessa pyytää sähköpostilla p@prr.fi

.
*&copy;Copyright 2013, Projekti Rajala. All rights reserved.*
