SRU Client for PHP
==================

A PHP Client library for [SRU](http://www.loc.gov/standards/sru/) servers.

Usage
-----

```
$client = new \SRU\Client('http://lx2.loc.gov:210/LCDB', ['recordSchema' => 'marcxml']);

$response = $client->searchRetrieve('dinosaur', ['maximumRecords' => 5]);

$response->numberOfRecords();
> 3212

$record = $response->getRecords()[0];
$record->schema();
> 'marcxml'

$record->position();
> 1
$response->nextPosition();
> 11

get_class($record->getData());
> DOMElement // <-- MARC record structure here

echo $record->getData(true); // Returns a string representation of the data

<record xmlns="http://www.loc.gov/MARC21/slim">
  <leader>01392cjm a2200325 a 4500</leader>
  <controlfield tag="001">18919847</controlfield>
  <controlfield tag="005">20160104074050.0</controlfield>
  <controlfield tag="007">sd fsngnnmmned</controlfield>
  <controlfield tag="008">151229s2012    gw mun|           | eng  </controlfield>
  <datafield tag="906" ind1=" " ind2=" ">
    <subfield code="a">7</subfield>
    <subfield code="b">cbc</subfield>
    <subfield code="c">orignew</subfield>
    <subfield code="d">2</subfield>
    <subfield code="e">ncip</subfield>
    <subfield code="f">20</subfield>
    <subfield code="g">y-soundrec</subfield>
  </datafield>
  <datafield tag="925" ind1="0" ind2=" ">
    <subfield code="a">acquire</subfield>
    <subfield code="b">2 shelf copies</subfield>
    <subfield code="x">policy default</subfield>
  </datafield>
  <datafield tag="955" ind1=" " ind2=" ">
    <subfield code="a">qr12 2015-12-29</subfield>
  </datafield>
  <datafield tag="010" ind1=" " ind2=" ">
    <subfield code="a">  2015662372</subfield>
  </datafield>
  <datafield tag="040" ind1=" " ind2=" ">
    <subfield code="a">DLC</subfield>
    <subfield code="c">DLC</subfield>
  </datafield>
  <datafield tag="041" ind1="0" ind2=" ">
    <subfield code="d">eng</subfield>
    <subfield code="d">ger</subfield>
  </datafield>
  <datafield tag="050" ind1="0" ind2="0">
    <subfield code="a">SDC 60883</subfield>
  </datafield>
  <datafield tag="245" ind1="0" ind2="0">
    <subfield code="a">1212</subfield>
    <subfield code="h">[sound recording] :</subfield>
    <subfield code="b">Dezember 2012.</subfield>
  </datafield>
  <datafield tag="246" ind1="1" ind2=" ">
    <subfield code="i">Title on container:</subfield>
    <subfield code="a">Nr. 1212</subfield>
  </datafield>
  <datafield tag="260" ind1=" " ind2=" ">
    <subfield code="a">[Berlin] :</subfield>
    <subfield code="b">Musikexpress,</subfield>
    <subfield code="c">[2012]</subfield>
  </datafield>
  <datafield tag="300" ind1=" " ind2=" ">
    <subfield code="a">1 sound disc :</subfield>
    <subfield code="b">digital ;</subfield>
    <subfield code="c">4 3/4 in.</subfield>
  </datafield>
  <datafield tag="511" ind1="0" ind2=" ">
    <subfield code="a">Various performers.</subfield>
  </datafield>
  <datafield tag="500" ind1=" " ind2=" ">
    <subfield code="a">"For promotion only!"</subfield>
  </datafield>
  <datafield tag="500" ind1=" " ind2=" ">
    <subfield code="a">Compact disc.</subfield>
  </datafield>
  <datafield tag="500" ind1=" " ind2=" ">
    <subfield code="a">Issued with the Dec. 2012 issue of Musikexpress.</subfield>
  </datafield>
  <datafield tag="505" ind1="0" ind2=" ">
    <subfield code="a">Elephant (Tame Impala) -- Partner in crime (Ecke Scho&#x308;nhauser) -- Peace of mind : Musikexpress edit (Fritz Kalkbrenner) -- I follow you (Melody's Echo Chamber) -- Dinosaur (Linnea Olsson) -- Long way to run (Bernhard Eder) -- Ich scha&#x308;me mich (Hans Unstern) -- Den Rosenkavalier (HGich.T) -- Mach mich traurig (Die Liga der Gewo&#x308;hnlichen Gentlemen) -- Wu&#x308;de Hund (Neigungsgruppe Sex, Gewalt &amp; Gute Laune).</subfield>
  </datafield>
  <datafield tag="650" ind1=" " ind2="0">
    <subfield code="a">Rock music</subfield>
    <subfield code="y">2011-2020.</subfield>
  </datafield>
  <datafield tag="650" ind1=" " ind2="0">
    <subfield code="a">Popular music</subfield>
    <subfield code="y">2011-2020.</subfield>
  </datafield>
  <datafield tag="655" ind1=" " ind2="7">
    <subfield code="a">Rock music.</subfield>
    <subfield code="2">lcgft</subfield>
  </datafield>
  <datafield tag="655" ind1=" " ind2="7">
    <subfield code="a">Popular music.</subfield>
    <subfield code="2">lcgft</subfield>
  </datafield>
  <datafield tag="730" ind1="0" ind2=" ">
    <subfield code="a">Musikexpress.</subfield>
  </datafield>
</record>
```
