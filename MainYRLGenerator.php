<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MainYRLGenerator
 *
 * @author SKYNET
 */
class MainYRLGenerator extends YRLGenerator {

    public function offers() {
        $adverts = Adverts::model()->findAll();
        foreach ($adverts as $key => $advert) {
            $arData = array(
                'type' => 'аренда',
                'property-type' => 'жилая',
                'category' => 'квартира',
                'url' => 'http://wwww.cdaem.ru/advert/' . $advert->id,
                'payed-adv' => $advert->spec,
                'manually-added' => '1',
                'creation-date' => date('Y-m-d\TH:i:s+03:00', strtotime($advert->DateT)),
                'location' => array(
                    'country' => 'Россия',
                    'locality-name' => 'Москва',
                    'sub-locality-name' => $advert->district->okrug_name,
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
            if (isset($advert->underground->station_name))
                    $arData['location']['metro'][] = array('name' => $advert->underground->station_name, 'time-on-foot' => $advert->udal_metro_peshkom, 'time-on-transport' => $advert->udal_metro_transport);
            if (isset($advert->underground2->station_name))
                    $arData['location']['metro'][] = array('name' => $advert->underground2->station_name, 'time-on-foot' => $advert->udal_metro_peshkom, 'time-on-transport' => $advert->udal_metro_transport);

            if (empty($arData['location']['metro']))
                    unset($arData['location']['metro']);

            if (!empty($advert->picture)) {
                foreach ($advert->picture as $pic) {
                    $arData['image'][] = 'http://www.cdaem.ru/images/' . $pic->id . '.jpg';
                }
            } else {
                unset($arData['image']);
            }

            $this->addOffer($advert->id, $arData);
        }
    }

}
