prr_svm2 - Suomen verkkomaksut moduli
===============

prr_svm2 v1.1
---------------

Suomen verkkomaksut maksumoduli ZenCart verkkokauppaan. Käyttää REST API:a.
Sisälää option integroida maksutavan valinnan suoraan zencartin kassaan (viimeinen vaihe).
Sisältää myös sivulaatikon, joka generoi kauppiastunnisteen perusteella käytössä olevat maksutapa ikonit näkyville (virallinen Suomen verkkomaksut "banneri").


Yhteensopivuus
-----------
Tämä moduli on testattu toimvaksi ZenCart 1.5.0 ja 1.5.1 versioissa.
Toimii myös 1.3.X versioissa, mutta vaatii että kauppa on UTF-8 merkistökoodattu tai että moduliin lisätään utf8_encode() funktio muuntamaan Suomen Verkkomaksuille lähetetyn datan UTF-8 muotoon.


Asennus
------------

1. Suorita oheinen install.sql tiedosto tietokantaan ZenCartin ylläpidossa olevalla SQL työkalulla.
2. Kopioi includes hakemisto kauppaan sellaisenaan. Asennus ei ylikirjoita tiedostoja.
   - Kopioi prr_svm_handler.php tiedosto kaupan juureen (eli sinne missä index.php sijaitsee)
3. Asenna moduli normaalisti ZenCartin ylläpidossa.
   - Oletusasetukset mahdollistavat modulin testauksen.
   - Aseta tilauksen tilat sopiviksi.
     * Ensimmäinen tila on se johon tilaus päätyy kun asiakas palaa SVM:n palvelusta verkkokauppaan.
     * Toinen tila määrittää mihin tilaan tilaus laitetaan kun SVM:n palvelin on lähettänyt maksuvahvistuksen verkkokauppaan.
   - Aseta ALV kanta jota käytetään "käsittelykuluihin".
     Käsittelykuluilla tarkoitetaan kaikkia ot_total (eli loppusummaan vaikuttavia) moduleita.
     ZenCart ei tallenna tätä tietoa tilausta luodessaan eikä sitä voida päätellä luotettavasti jälkikäteen.


Loki
------------
Moduli tallentaa logs/ (tai cache/ jos logs hakemistoa ei ole) kaikki virheelliset maksuvahvistus yritykset. Tulivat ne sitten Suomen verkkomaksuilta tai joltain muulta taholta (mahdollinen huijaus yritys).


Tuki
------------
Tukea ja modulin kustomointia voi tarvittaessa pyytää sähköpostilla p@prr.fi

Mikäli haluat tukea tämän modulin kehitystä, käytä jälleenmyyjätunnusta **238540** kun rekisteröidyt Suomen Verkkomaksujen käyttäjäksi.
Tämä ei maksa sinulle mitään, mutta antaa minulle muutaman euron. Kiitos!
Voit käyttää rekisteröitymiseen myös linkkiä: https://ssl.verkkomaksut.fi/register/index/index/rid/238540


------------
*Copyright &copy; 2013, Projekti Rajala*
