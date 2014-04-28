<?php
/**
 * API:  https://developers.google.com/places/documentation/supported_types
 * @author      Tom Lous <tomlous@gmail.com>
 * @copyright   2014 Tom Lous
 * @package     package
 * Datetime:     28/04/14 16:02
 */

namespace DataInterface\GoogleMaps;


class GoogleMapsPlaceTypes {
    // https://developers.google.com/places/documentation/supported_types
    const accounting = 'accounting';
    const airport = 'airport';
    const amusement_park = 'amusement_park';
    const aquarium = 'aquarium';
    const art_gallery = 'art_gallery';
    const atm = 'atm';
    const bakery = 'bakery';
    const bank = 'bank';
    const bar = 'bar';
    const beauty_salon = 'beauty_salon';
    const bicycle_store = 'bicycle_store';
    const book_store = 'book_store';
    const bowling_alley = 'bowling_alley';
    const bus_station = 'bus_station';
    const cafe = 'cafe';
    const campground = 'campground';
    const car_dealer = 'car_dealer';
    const car_rental = 'car_rental';
    const car_repair = 'car_repair';
    const car_wash = 'car_wash';
    const casino = 'casino';
    const cemetery = 'cemetery';
    const church = 'church';
    const city_hall = 'city_hall';
    const clothing_store = 'clothing_store';
    const convenience_store = 'convenience_store';
    const courthouse = 'courthouse';
    const dentist = 'dentist';
    const department_store = 'department_store';
    const doctor = 'doctor';
    const electrician = 'electrician';
    const electronics_store = 'electronics_store';
    const embassy = 'embassy';
    const establishment = 'establishment';
    const finance = 'finance';
    const fire_station = 'fire_station';
    const florist = 'florist';
    const food = 'food';
    const funeral_home = 'funeral_home';
    const furniture_store = 'furniture_store';
    const gas_station = 'gas_station';
    const general_contractor = 'general_contractor';
    const grocery_or_supermarket = 'grocery_or_supermarket';
    const gym = 'gym';
    const hair_care = 'hair_care';
    const hardware_store = 'hardware_store';
    const health = 'health';
    const hindu_temple = 'hindu_temple';
    const home_goods_store = 'home_goods_store';
    const hospital = 'hospital';
    const insurance_agency = 'insurance_agency';
    const jewelry_store = 'jewelry_store';
    const laundry = 'laundry';
    const lawyer = 'lawyer';
    const library = 'library';
    const liquor_store = 'liquor_store';
    const local_government_office = 'local_government_office';
    const locksmith = 'locksmith';
    const lodging = 'lodging';
    const meal_delivery = 'meal_delivery';
    const meal_takeaway = 'meal_takeaway';
    const mosque = 'mosque';
    const movie_rental = 'movie_rental';
    const movie_theater = 'movie_theater';
    const moving_company = 'moving_company';
    const museum = 'museum';
    const night_club = 'night_club';
    const painter = 'painter';
    const park = 'park';
    const parking = 'parking';
    const pet_store = 'pet_store';
    const pharmacy = 'pharmacy';
    const physiotherapist = 'physiotherapist';
    const place_of_worship = 'place_of_worship';
    const plumber = 'plumber';
    const police = 'police';
    const post_office = 'post_office';
    const real_estate_agency = 'real_estate_agency';
    const restaurant = 'restaurant';
    const roofing_contractor = 'roofing_contractor';
    const rv_park = 'rv_park';
    const school = 'school';
    const shoe_store = 'shoe_store';
    const shopping_mall = 'shopping_mall';
    const spa = 'spa';
    const stadium = 'stadium';
    const storage = 'storage';
    const store = 'store';
    const subway_station = 'subway_station';
    const synagogue = 'synagogue';
    const taxi_stand = 'taxi_stand';
    const train_station = 'train_station';
    const travel_agency = 'travel_agency';
    const university = 'university';
    const veterinary_care = 'veterinary_care';
    const zoo = 'zoo';


    public static function getFoodServiceTypes(){
        return array(
            self::bar,
            self::cafe,
            self::food,
            self::gas_station,
            self::lodging,
            self::meal_delivery,
            self::meal_takeaway,
            self::restaurant,
        );
    }


    public static function getReligiousBuildingTypes(){
        return array(
            self::church,
            self::hindu_temple,
            self::mosque,
            self::place_of_worship,
            self::synagogue,
        );
    }

    public static function getTransportationTypes(){
        return array(
            self::airport,
            self::bus_station,
            self::taxi_stand,
            self::subway_station,
            self::train_station,
            self::parking,
        );
    }


    public static function getRecreationalTypes(){
        return array(
            self::amusement_park,
            self::aquarium,
            self::art_gallery,
            self::bowling_alley,
            self::campground,
            self::casino,
            self::museum,
            self::night_club,
            self::spa,
            self::stadium,
            self::zoo,
            self::movie_theater,
            self::park,
            self::rv_park,

        );
    }

    public static function getEducationTypes(){
        return array(
            self::university,
            self::school,
            self::library,
        );
    }

    public static function getStoreTypes(){
        return array(
            self::bakery,
            self::bank,
            self::beauty_salon,
            self::bicycle_store,
            self::book_store,
            self::car_dealer,
            self::car_rental,
            self::clothing_store,
            self::convenience_store,
            self::department_store,
            self::electronics_store,
            self::florist,
            self::furniture_store,
            self::hardware_store,
            self::home_goods_store,
            self::grocery_or_supermarket,
            self::liquor_store,
            self::movie_rental,
            self::jewelry_store,
            self::laundry,
            self::locksmith,
            self::pet_store,
            self::pharmacy,
            self::real_estate_agency,
            self::shoe_store,
            self::shopping_mall,
            self::store,
            self::travel_agency,
            self::hair_care,
        );
    }

    public static function getServicesTypes(){
        return array(
            self::car_repair,
            self::car_wash,
            self::funeral_home,
            self::electrician,
            self::general_contractor,
            self::finance,
            self::post_office,
            self::fire_station,
            self::police,
            self::insurance_agency,
            self::lawyer,
            self::gym,
            self::roofing_contractor,
            self::moving_company,
            self::painter,
            self::physiotherapist,
            self::plumber,
            self::storage,
            self::dentist,
            self::doctor,
            self::health,
            self::hospital,
            self::veterinary_care,
            self::gas_station,
        );
    }


    public static function getGovernmentTypes(){
        return array(
            self::city_hall,
            self::courthouse,
            self::embassy,
            self::local_government_office,
        );
    }


    /*
     self::accounting,
            self::airport,
            self::amusement_park,
            self::aquarium,
            self::art_gallery,
            self::atm,
            self::bakery,
            self::bank,
            self::bar,
            self::beauty_salon,
            self::bicycle_store,
            self::book_store,
            self::bowling_alley,
            self::bus_station,
            self::cafe,
            self::campground,
            self::car_dealer,
            self::car_rental,
            self::car_repair,
            self::car_wash,
            self::casino,
            self::cemetery,
            self::church,
            self::city_hall,
            self::clothing_store,
            self::convenience_store,
            self::courthouse,
            self::dentist,
            self::department_store,
            self::doctor,
            self::electrician,
            self::electronics_store,
            self::embassy,
            self::establishment,
            self::finance,
            self::fire_station,
            self::florist,
            self::food,
            self::funeral_home,
            self::furniture_store,
            self::gas_station,
            self::general_contractor,
            self::grocery_or_supermarket,
            self::gym,
            self::hair_care,
            self::hardware_store,
            self::health,
            self::hindu_temple,
            self::home_goods_store,
            self::hospital,
            self::insurance_agency,
            self::jewelry_store,
            self::laundry,
            self::lawyer,
            self::library,
            self::liquor_store,
            self::local_government_office,
            self::locksmith,
            self::lodging,
            self::meal_delivery,
            self::meal_takeaway,
            self::mosque,
            self::movie_rental,
            self::movie_theater,
            self::moving_company,
            self::museum,
            self::night_club,
            self::painter,
            self::park,
            self::parking,
            self::pet_store,
            self::pharmacy,
            self::physiotherapist,
            self::place_of_worship,
            self::plumber,
            self::police,
            self::post_office,
            self::real_estate_agency,
            self::restaurant,
            self::roofing_contractor,
            self::rv_park,
            self::school,
            self::shoe_store,
            self::shopping_mall,
            self::spa,
            self::stadium,
            self::storage,
            self::store,
            self::subway_station,
            self::synagogue,
            self::taxi_stand,
            self::train_station,
            self::travel_agency,
            self::university,
            self::veterinary_care,
            self::zoo,
     */

} 