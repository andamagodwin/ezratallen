<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FarmsellAgent extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'knowing_farmsell',
        'motivation',
        'soft_profesional_skills',
        'experience',
        'education',
        'social_media_numbers',
        'contact_details',
        'personal_details',
        'form_stage'
    ];


    public function getPersonalDetailsAttribute($value)
    {
        if ($value) {
            return json_decode($value);
        }
    }

    public function getKnowingFarmsellAttribute($value)
    {
        if ($value) {
            return json_decode($value);
        }
    }
    public function getMotivationAttribute($value)
    {
        if ($value) {
            return json_decode($value);
        }
    }
    public function getSoftProfesionalSkillsAttribute($value)
    {
        if ($value) {
            return json_decode($value);
        }
    }
    public function getExperienceAttribute($value)
    {
        if ($value) {
            return json_decode($value);
        }
    }
    public function getEducationAttribute($value)
    {
        if ($value) {
            return json_decode($value);
        }
    }
    public function getSocialMediaNumbersAttribute($value)
    {
        if ($value) {
            return json_decode($value);
        }
    }
    public function getContactDetailsAttribute($value)
    {
        if ($value) {
            return json_decode($value);
        }
    }
    public function getCreatedAtAttribute($value)
    {
        //$format = 'd/m/Y';



        return Carbon::parse($value)->format('d M Y, g:i A');
    }
}
