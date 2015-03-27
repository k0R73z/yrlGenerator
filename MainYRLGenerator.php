<?php

class MyYRLGenerator extends YRLGenerator {

    public function offers() {
        $adverts = Adverts::model()->findAll();
        foreach ($adverts as $advert) {
            $arData = array(
                'type' => 'аренда',
                'property-type' => 'жилая',
                'category' => 'квартира',
                'url' => $advert->url,
                'payed-adv' => $advert->spec,
                'manually-added' => '1',
                'creation-date' => date('Y-m-d\TH:i:s+03:00', strtotime($advert->DateT)),
                'location' => array(
                    'country' => 'Россия',
                    'locality-name' => 'Москва',
                    'sub-locality-name' => $advert->district->area,
                    'address' => $advert->geopoint,
                    'metro' => array(
                    ),
                ),
                'image' => array(
                ),
                'description' => strip_tags($advert->description),
                'area' => array(
                    'value' => $advert->square,
                    'unit' => 'кв.м',
                ),
                'rooms' => $advert->number_flat,
            );

            foreach ($advert->metro as $metro) {
                $arData['location']['metro'][] = array(
                    'name' => $metro->station_name,
                    'time-on-foot' => $metro->walk_time,
                    'time-on-transport' => $metro->auto_time
                );
            }
            foreach ($advert->picture as $pic) {
                $arData['image'][] = $pic->src;
            }
            
            // Убираем пустые элементы как сказано в требованиях к фиду
            if (empty($arData['location']['metro'])) {
                unset($arData['location']['metro']);
            }
            if (empty($arData['image'])) {
                unset($arData['image']);
            }

            $this->addOffer($advert->id, $arData);
        }
    }

}
