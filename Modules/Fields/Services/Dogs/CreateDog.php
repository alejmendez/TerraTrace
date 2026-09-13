<?php

namespace Modules\Fields\Services\Dogs;

use Modules\Fields\Models\Dog;
use Modules\Fields\Models\DogVaccine;

class CreateDog
{
    public static function call($data): Dog
    {
        $dog = new Dog;
        $dog->name = $data['name'];
        $dog->breed = $data['breed'];
        $dog->gender = $data['gender']['value'];
        $dog->birthdate = $data['birthdate'];
        $dog->veterinary = $data['veterinary'];
        $dog->field_id = $data['field_id']['value'];
        $dog->couple_id = $data['couple_id']['value'];
        $dog->avatar = $data['avatar'];

        $dog->save();

        foreach ($data['vaccines'] as $vaccine) {
            if ($vaccine['name'] == null && $vaccine['date'] == null) {
                continue;
            }
            $dog_vaccine = new DogVaccine;
            $dog_vaccine->name = $vaccine['name'];
            $dog_vaccine->date = $vaccine['date'];
            $dog_vaccine->dog_id = $dog->id;
            $dog_vaccine->save();
        }

        return $dog;
    }
}
