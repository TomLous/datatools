<?php
/**
 * @author      Tom Lous <tomlous@gmail.com>
 * @copyright   2014 Tom Lous
 * @package     DataInterface
 * Datetime:     28/04/14 10:40
 * http://msdn.microsoft.com/en-us/library/ff701734.aspx
 */

namespace DataInterface;

use DataInterface\Exception\IncompatibleInterfaceException;
use DataInterface\Exception\IncompatibleInputException;
use models\Address;
use models\GeoLocation;

class BingMapsNavteqSpatialSearch extends DataInterface
{

    const DataSourceEU = 'NavteqEU';
    const DataSourceNA = 'NavteqNA';

    /**
     * POI Entity Types
     * http://msdn.microsoft.com/en-us/library/hh478191.aspx
     */
    const Winery = 2084; // Winery
    const ATM = 3578; // ATM
    const TrainStation = 4013; // Train Station
    const CommuterRailStation = 4100; // Commuter Rail Station
    const BusStation = 4170; // Bus Station
    const NamedPlace = 4444; // Named Place
    const FerryTerminal = 4482; // Ferry Terminal
    const Marina = 4493; // Marina
    const PublicSportsAirport = 4580; // Public Sports Airport
    const Airport = 4581; // Airport
    const BusinessFacility = 5000; // Business Facility
    const GroceryStore = 5400; // Grocery Store
    const AutoDealerships = 5511; // Auto Dealerships
    const AutoDealershipUsedCars = 5512; // Auto Dealership-Used Cars
    const PetrolGasolineStation = 5540; // Petrol/Gasoline Station
    const MotorcycleDealership = 5571; // Motorcycle Dealership
    const Restaurant = 5800; // Restaurant
    const Nightlife = 5813; // Nightlife
    const HistoricalMonument = 5999; // Historical Monument
    const Bank = 6000; // Bank
    const Shopping = 6512; // Shopping
    const Hotel = 7011; // Hotel
    const SkiResort = 7012; // Ski Resort
    const OtherAccommodation = 7013; // Other Accommodation
    const SkiLift = 7014; // Ski Lift
    const TouristInformation = 7389; // Tourist Information
    const RentalCarAgency = 7510; // Rental Car Agency
    const ParkingLot = 7520; // Parking Lot
    const ParkingGarageHouse = 7521; // Parking Garage/House
    const ParkRide = 7522; // Park & Ride
    const AutoServiceMaintenance = 7538; // Auto Service & Maintenance
    const Cinema = 7832; // Cinema
    const RestArea = 7897; // Rest Area
    const PerformingArts = 7929; // Performing Arts
    const BowlingCentre = 7933; // Bowling Centre
    const SportsComplex = 7940; // Sports Complex
    const ParkRecreationArea = 7947; // Park/Recreation Area
    const Casino = 7985; // Casino
    const ConventionExhibitionCentre = 7990; // Convention/Exhibition Centre
    const GolfCourse = 7992; // Golf Course
    const CivicCommunityCentre = 7994; // Civic/Community Centre
    const AmusementPark = 7996; // Amusement Park
    const SportsCentre = 7997; // Sports Centre
    const IceSkatingRink = 7998; // Ice Skating Rink
    const TouristAttraction = 7999; // Tourist Attraction
    const Hospital = 8060; // Hospital
    const HigherEducation = 8200; // Higher Education
    const School = 8211; // School
    const Library = 8231; // Library
    const Museum = 8410; // Museum
    const AutomobileClub = 8699; // Automobile Club
    const CityHall = 9121; // City Hall
    const CourtHouse = 9211; // Court House
    const PoliceStation = 9221; // Police Station
    const BusinessService = 9500; // Business Service
    const OtherCommunication = 9501; // Other Communication
    const TelephoneService = 9502; // Telephone Service
    const CleaningLaundry = 9503; // Cleaning & Laundry
    const HairBeauty = 9504; // Hair & Beauty
    const HealthCareService = 9505; // Health Care Service
    const Mover = 9506; // Mover
    const Photography = 9507; // Photography
    const VideoGameRental = 9508; // Video & Game Rental
    const Storage = 9509; // Storage
    const TailorAlteration = 9510; // Tailor & Alteration
    const TaxService = 9511; // Tax Service
    const RepairService = 9512; // Repair Service
    const RetirementNursingHome = 9513; // Retirement/Nursing Home
    const SocialService = 9514; // Social Service
    const Utilities = 9515; // Utilities
    const WasteSanitary = 9516; // Waste & Sanitary
    const Campground = 9517; // Campground
    const AutoParts = 9518; // Auto Parts
    const CarWashDetailing = 9519; // Car Wash/Detailing
    const LocalTransit = 9520; // Local Transit
    const TravelAgentTicketing = 9521; // Travel Agent & Ticketing
    const TruckStopPlaza = 9522; // Truck Stop/Plaza
    const Church = 9523; // Church
    const Synagogue = 9524; // Synagogue
    const GovernmentOffice = 9525; // Government Office
    const FireDepartment = 9527; // Fire Department
    const RoadAssistance = 9528; // Road Assistance
    const FuneralDirector = 9529; // Funeral Director
    const PostOffice = 9530; // Post Office
    const BanquetHall = 9531; // Banquet Hall
    const BarorPub = 9532; // Bar or Pub
    const CocktailLounge = 9533; // Cocktail Lounge
    const NightClub = 9534; // Night Club
    const ConvenienceStore = 9535; // Convenience Store
    const SpecialtyFoodStore = 9536; // Specialty Food Store
    const ClothingStore = 9537; // Clothing Store
    const MensApparel = 9538; // Men's Apparel
    const ShoeStore = 9539; // Shoe Store
    const SpecialtyClothingStore = 9540; // Specialty Clothing Store
    const WomensApparel = 9541; // Women's Apparel
    const CheckCashingService = 9542; // Check Cashing Service
    const CurrencyExchange = 9543; // Currency Exchange
    const MoneyTransferringService = 9544; // Money Transferring Service
    const DepartmentStore = 9545; // Department Store
    const DiscountStore = 9546; // Discount Store
    const OtherGeneralMerchandise = 9547; // Other General Merchandise
    const VarietyStore = 9548; // Variety Store
    const GardenCenter = 9549; // Garden Center
    const GlassWindow = 9550; // Glass & Window
    const HardwareStore = 9551; // Hardware Store
    const HomeCenter = 9552; // Home Center
    const Lumber = 9553; // Lumber
    const OtherHouseGarden = 9554; // Other House & Garden
    const Paint = 9555; // Paint
    const EntertainmentElectronics = 9556; // Entertainment Electronics
    const FloorCarpet = 9557; // Floor & Carpet
    const FurnitureStore = 9558; // Furniture Store
    const MajorAppliance = 9559; // Major Appliance
    const HomeSpecialtyStore = 9560; // Home Specialty Store
    const ComputerSoftware = 9561; // Computer & Software
    const FlowersJewelry = 9562; // Flowers & Jewelry
    const GiftAntiqueArt = 9563; // Gift, Antique, & Art
    const Optical = 9564; // Optical
    const Pharmacy = 9565; // Pharmacy
    const RecordCDVideo = 9566; // Record, CD, & Video
    const SpecialtyStore = 9567; // Specialty Store
    const SportingGoodsStore = 9568; // Sporting Goods Store
    const WineLiquor = 9569; // Wine & Liquor
    const Boating = 9570; // Boating
    const Theater = 9571; // Theater
    const RaceTrack = 9572; // Race Track
    const GolfPracticeRange = 9573; // Golf Practice Range
    const HealthClub = 9574; // Health Club
    const BowlingAlley = 9575; // Bowling Alley
    const SportsActivities = 9576; // Sports Activities
    const RecreationCenter = 9577; // Recreation Center
    const Attorney = 9578; // Attorney
    const Dentist = 9579; // Dentist
    const Physician = 9580; // Physician
    const Realtor = 9581; // Realtor
    const RVPark = 9582; // RV Park
    const MedicalService = 9583; // Medical Service
    const PoliceService = 9584; // Police Service
    const VeterinarianService = 9585; // Veterinarian Service
    const SportingInstructionalCamp = 9586; // Sporting & Instructional Camp
    const AgriculturalProductMarket = 9587; // Agricultural Product Market
    const PublicRestroom = 9589; // Public Restroom
    const ResidentialAreaBuilding = 9590; // Residential Area/Building
    const Cemetery = 9591; // Cemetery
    const HighwayExit = 9592; // Highway Exit
    const TransportationService = 9593; // Transportation Service
    const LotteryBooth = 9594; // Lottery Booth
    const PublicTransitStop = 9707; // Public Transit Stop
    const PublicTransitAccess = 9708; // Public Transit Access
    const Neighborhood = 9709; // Neighborhood
    const WeighStation = 9710; // Weigh Station
    const CargoCentre = 9714; // Cargo Centre
    const MilitaryBase = 9715; // Military Base
    const Tollbooth = 9717; // Tollbooth (China/Korea)
    const AnimalPark = 9718; // Animal Park
    const TruckDealership = 9719; // Truck Dealership
    const TruckParking = 9720; // Truck Parking
    const HomeImprovementHardwareStore = 9986; // Home Improvement & Hardware Store
    const ConsumerElectronicsStore = 9987; // Consumer Electronics Store
    const OfficeSupplyServicesStore = 9988; // Office Supply & Services Store
    const TaxiStand = 9989; // Taxi Stand
    const PremiumDefault = 9990; // Premium Default
    const IndustrialZone = 9991; // Industrial Zone
    const PlaceofWorship = 9992; // Place of Worship
    const Embassy = 9993; // Embassy
    const CountyCouncil = 9994; // County Council
    const Bookstore = 9995; // Bookstore
    const CoffeeShop = 9996; // Coffee Shop
    const Hamlet = 9998; // Hamlet
    const BorderCrossing = 9999; // Border Crossing



    public function test()
    {

    }

    // @todo under development
} 