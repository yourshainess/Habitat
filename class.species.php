<?php

class Species implements SplObserver{
	public $type;
	public $current_age; // age in months
	public $thirst_level;
	public $hunger_level;
	public $hot_level;
	public $cold_level;

	private $is_preg; // bool
	private $incubation_time; // in months

	private $gender; // male or female
	private $_gestration_period; // in months
	private $_life_span; // in years
	private $_minimum_breeding_age; //in years
	private $_maximum_breeding_age; // in years

	private $_monthly_food_consumption;
	private $_monthly_water_consumption;
	private $_minimum_temperature;
	private $_maximum_temperature;

	const max_hunger_level = 4;
	const max_thirst_level = 2;
	const max_hot_level = 2;
	const max_cold_level = 2;

	public function __construct ($init_type, $init_gender = false){
		$this->type = strtolower($init_type);
		$this->current_age = 0;
		$this->thirst_level = 0;
		$this->hunger_level = 0;
		$this->is_preg = false;
		if( !$init_gender AND $init_gender != 'male' AND $init_gender != 'female' ){
			switch(rand(1, 2)){
				case 1:
					$this->gender = 'male';
				break;
				default:
					$this->gender = 'female';
				break;
			}
		}else{
			$this->gender = $init_gender;
		}
	}

	public function get_type() {
		return $this->type;
	}

	public function age(){
		$this->current_age++;
		if( $this->current_age > $this->_life_span * 12 )
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	public function get_age(){
		return $this->current_age;
	}

	public function get_gender(){
		return $this->gender;
	}

	public function set_gestration_period($init_time) {
		$this->_gestration_period = abs(intval($init_time));
	}

	public function get_gestration_period() {
		return $this->_gestration_period;
	}

	public function set_life_span($init_time) {
		$this->_life_span = abs(intval($init_time));
	}

	public function get_life_span() {
		return $this->_life_span;
	}

	public function set_minimum_breeding_age($init_time) {
		$this->_minimum_breeding_age = abs(intval($init_time));
	}

	public function get_minimum_breeding_age() {
		return $this->_minimum_breeding_age;
	}

	public function set_maximum_breeding_age($init_time) {
		$this->_maximum_breeding_age = abs(intval($init_time));
	}

	public function get_maximum_breeding_age() {
		return $this->_maximum_breeding_age;
	}

	public function set_minimum_temperature($init_unit) {
		$this->_minimum_temperature = (intval($init_unit));
	}

	public function get_minimum_temperature() {
		return $this->_minimum_temperature;
	}

	public function set_maximum_temperature($init_unit) {
		$this->_maximum_temperature = (intval($init_unit));
	}

	public function get_maximum_temperature() {
		return $this->_maximum_temperature;
	}

	public function set_monthly_food_consumption($init_amount) {
		$this->_monthly_food_consumption = abs(intval($init_amount));
	}

	public function get_monthly_food_consumption() {
		return $this->_monthly_food_consumption;
	}

	public function set_monthly_water_consumption($init_amount) {
		$this->_monthly_water_consumption = abs(intval($init_amount));
	}

	public function get_monthly_water_consumption() {
		return $this->_monthly_water_consumption;
	}

	public function consume_food(SplSubject $habitat){
		if( $habitat->get_food_supply() >= $this->_monthly_food_consumption){
			$habitat->deplete_food_supply($this->_monthly_food_consumption);
			$this->hunger_level = 0;
			return true;
		}else{
			$this->hunger_level++;
			if($this->hunger_level >= self::max_hunger_level)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
	}

	public function drink_water(SplSubject $habitat){
		if( $habitat->get_water_supply() >= $this->_monthly_water_consumption){
			$habitat->deplete_water_supply($this->_monthly_water_consumption);
			$this->thirst_level = 0;
			return true;
		}else{
			$this->thirst_level++;
			if($this->thirst_level >= self::max_thirst_level)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
	}

	public function survive_hot_temperature(SplSubject $habitat){
		$current_temperature = $habitat->get_current_temperature();
		if( $current_temperature > $this->_maximum_temperature ){
			$this->hot_level++;
			if($this->hot_level >= self::max_hot_level)
			{
				return false;
			}
			else
			{
				return true;
			}
		}else{
			return true;
		}
	}

	public function survive_cold_temperature(SplSubject $habitat){
		$current_temperature = $habitat->get_current_temperature();
		if( $current_temperature < $this->_minimum_temperature ){
			$this->cold_level++;
			if($this->cold_level >= self::max_cold_level)
			{
				return false;
			}
			else
			{
				return true;
			}
		}else{
			return true;
		}
	}

	public function reproduce(SplSubject $habitat){
		if($this->gender=='female' AND $this->is_preg){
			$this->incubation_time++;
			if($this->incubation_time > $this->_gestration_period){
				$this->is_preg = false;
				$this->incubation_time = 0;
				/* this will be the only time this function should return true, when a new life is born */
				return true;
			}
		}

		if($this->gender=='female' AND !$this->is_preg AND $this->current_age >= ($this->_minimum_breeding_age * 12) AND $this->current_age <= ($this->_maximum_breeding_age * 12)){
			if( $habitat->get_can_support_new_life() AND $habitat->get_male_population() > 0 ){
				$this->is_preg = true;
				$this->incubation_time = 0;
			}

			if( !$habitat->get_can_support_new_life() AND $habitat->get_male_population() > 0 ){
				$randomizer = rand (1, 10000);
				if($randomizer > 9995){
					$this->is_preg = true;
					$this->incubation_time = 0;
				}
			}
		}

		return false;
	}

	public function update(SplSubject $habitat){
		if( !$this->age() ){
			return '_oldage_';
		}

		if( !$this->consume_food($habitat) ){
			return '_starvation_';
		}

		if( !$this->drink_water($habitat) ){
			return '_thirst_';
		}

		if( ! $this->survive_hot_temperature($habitat) ){
			return '_hottemperature_';
		}

		if( !$this->survive_cold_temperature($habitat) ){
			return '_coldtemperature_';
		}

		if($this->reproduce($habitat)){
			return '_newlife_';
		}

		return '_normal_';
	}

	public function spawn(){
		$newborn = new Species($this->type);
		$newborn->set_gestration_period($this->_gestration_period);
		$newborn->set_life_span($this->_life_span);
		$newborn->set_minimum_breeding_age($this->_minimum_breeding_age);
		$newborn->set_maximum_breeding_age($this->_maximum_breeding_age);
		$newborn->set_monthly_food_consumption($this->_monthly_food_consumption);
		$newborn->set_monthly_water_consumption($this->_monthly_water_consumption);
		$newborn->set_minimum_temperature($this->_minimum_temperature);
		$newborn->set_maximum_temperature($this->_maximum_temperature);

		return $newborn;
	}
}

?>