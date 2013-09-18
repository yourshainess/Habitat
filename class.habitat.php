<?php

class Habitat implements SplSubject {
	private $habitat_name;
	private $time_interval; //time measured in months
	
	public $food_supply;
	public $water_supply;
	private $_replenish_food_amount;
	private $_replenish_water_amount;
	
	private $male_population;
	private $population;
	
	private $current_temperature;
	private $seasonal_temperature;
	
	private $can_support_new_life = true;
	private $deaths;

	function __construct($init_name, $init_food, $init_water, array $init_temperature)
	{
		$this->habitat_name = $init_name;
		
		$this->population = new SplObjectStorage();
		$this->food_supply = 0;
		$this->water_supply = 0;
		$this->_replenish_food_amount = $init_food;
		$this->_replenish_water_amount = $init_water;
		
		$this->current_temperature = 0;
		$this->seasonal_temperature = $init_temperature;

		$this->time_interval = 0;
		
		$this->deaths['_oldage_'] = 0;
		$this->deaths['_starvation_'] = 0;
		$this->deaths['_thirst_'] = 0;
		$this->deaths['_hottemperature_'] = 0;
		$this->deaths['_coldtemperature_'] = 0;
	}
	
	function init()
	{
		$this->set_time_interval();
		$this->replenish_supply();
		$this->set_current_temperature();
		
		$current_population = sizeof($this->population);
		
		$this->can_support_new_life = true;
		$this->population->rewind();
		if($current_population > 0){
			$total_food_comsumption = $current_population * $this->population->current()->get_monthly_food_consumption();
			$total_water_comsumption = $current_population * $this->population->current()->get_monthly_water_consumption();
			if($total_food_comsumption <= $this->food_supply AND $total_water_comsumption <= $this->water_supply ){
				$this->can_support_new_life = true;
			}else{
				$this->can_support_new_life = false;
			}
		}
	}
	
	function get_population()
	{
		return sizeof($this->population);
	}
	
	function get_mortality()
	{
		return $this->deaths;
	}
	
	function get_can_support_new_life()
	{
		return $this->can_support_new_life;
	}
	
	function get_male_population()
	{
		return $this->male_population;
	}
	
	function get_habitat_name()
	{
		return $this->name;
	}

	function set_time_interval()
	{
		$this->time_interval++;
	}

	function get_time_interval()
	{
		$this->time_interval++;
	}

	function get_current_temperature()
	{
		return $this->current_temperature;
	}

	function replenish_supply()
	{
		$this->food_supply  += $this->_replenish_food_amount;
		$this->water_supply += $this->_replenish_water_amount;
	}

	function get_food_supply()
	{
		return $this->food_supply;
	}

	function get_water_supply() {
		return $this->water_supply;
	}
	
	function deplete_food_supply($deplete_amount)
	{
		$this->food_supply -= abs(intval($deplete_amount));
	}
	
	function deplete_water_supply($deplete_amount)
	{
		$this->water_supply -= abs(intval($deplete_amount));
	}
	
	function set_current_temperature()
	{
		$average_seasonal_temperature = 0;
		switch($this->time_interval % 12){
			case 0:
			case 1:
			case 2:
				$average_seasonal_temperature = $this->seasonal_temperature['winter'];
				break;
			case 3:
			case 4:
			case 5:
				$average_seasonal_temperature = $this->seasonal_temperature['spring'];
				break;
			case 6:
			case 7:
			case 8:
				$average_seasonal_temperature = $this->seasonal_temperature['summer'];
				break;
			case 9:
			case 10:
			case 11:
				$average_seasonal_temperature = $this->seasonal_temperature['fall'];
				break;
		}
		
		$randomizer = rand(1,10000);
		
		if($randomizer <= 25){
			$fluctuation = -15;
		}else if($randomizer >= 9975){
			$fluctuation = 15;
		}else{
			$fluctuation = rand(0, 10) - 5;
		}
		
		$this->current_temperature = $average_seasonal_temperature + $fluctuation;
	}
	
	public function attach( SplObserver $specie)
	{
		if($specie->get_gender() == 'male') {
			$this->male_population++;
		}
		
		$this->population->attach( $specie );
	}
	
	public function detach( SplObserver $specie )
	{
		if($specie->get_gender() == 'male'){
			$this->male_population--;
		}
		$this->population->detach( $specie );
	}
	
	public function notify()
	{
		$new_population = array();
		foreach ($this->population as $specie ) {
			$status = $specie->update( $this );
			switch($status) {
				case '_oldage_':
					//debug( '_oldage_ ' . $specie->get_age() );
					$this->detach( $specie );
					$this->deaths['_oldage_']++;
					break;
				case '_starvation_':
					//debug( '_starvation_ ' );
					$this->detach( $specie );
					$this->deaths['_starvation_']++;
					break;
				case '_thirst_':
					//debug( '_thirst_ ' );
					$this->detach( $specie );
					$this->deaths['_thirst_']++;
					break;
				case '_hottemperature_':
					//debug( '_hottemperature_ ' );
					$this->detach( $specie );
					$this->deaths['_hottemperature_']++;
					break;
				case '_coldtemperature_':
					//debug( '_coldtemperature_ ' );
					$this->detach( $specie );
					$this->deaths['_coldtemperature_']++;
					break;
				case '_newlife_':
					$new_population[] = $specie->spawn();
					break;
			}
		}
		
		foreach($new_population as $new_specie){
			$this->attach($new_specie);
		}
		
		/* if the whole population is wiped out we tell the habitat we no longer need to run */
		if(sizeof($this->population) < 1){
			return false;
		}
		
		return true;
	}
}

?>