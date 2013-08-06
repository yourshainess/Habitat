<?php

require 'config.php';
require 'spyc.php';
require 'class.habitat.php';
require 'class.species.php';

$configuration = Spyc::YAMLLoad('config.yaml');

$runtime_years = $configuration['years'];
$runtime_iterations = $configuration['iterations'];
$species_configs = $configuration['species'];
$habitat_configs = $configuration['habitats'];
$runtime_months = $runtime_years * 12;

echo 'Simulation ran for '.$runtime_iterations.' iterations at '.$runtime_years.' years per iteration<br>';
foreach($species_configs as $species_config){
	echo $species_config['name'].':<br>';

	foreach($habitat_configs as $habitat_config){
		$total_habitat_population = 0;
		$max_habitat_population = 0;

		echo "\t". $habitat_config['name'].':<br>';
		for($i=0; $i<$runtime_iterations; $i++){

			$habitat = new Habitat($habitat_config['name'], $habitat_config['monthly_food'], $habitat_config['monthly_water'], $habitat_config['average_temperature']);
			$male_specie = new Species($species_config['name'], 'male');
			$male_specie->set_gestration_period($species_config['attributes']['gestation_period']);
			$male_specie->set_life_span($species_config['attributes']['life_span']);
			$male_specie->set_minimum_breeding_age($species_config['attributes']['minimum_breeding_age']);
			$male_specie->set_maximum_breeding_age($species_config['attributes']['maximum_breeding_age']);
			$male_specie->set_monthly_food_consumption($species_config['attributes']['monthly_food_consumption']);
			$male_specie->set_monthly_water_consumption($species_config['attributes']['monthly_water_consumption']);
			$male_specie->set_minimum_temperature($species_config['attributes']['minimum_temperature']);
			$male_specie->set_maximum_temperature($species_config['attributes']['maximum_temperature']);

			$female_specie = new Species($species_config['name'], 'female');
			$female_specie->set_gestration_period($species_config['attributes']['gestation_period']);
			$female_specie->set_life_span($species_config['attributes']['life_span']);
			$female_specie->set_minimum_breeding_age($species_config['attributes']['minimum_breeding_age']);
			$female_specie->set_maximum_breeding_age($species_config['attributes']['maximum_breeding_age']);
			$female_specie->set_monthly_food_consumption($species_config['attributes']['monthly_food_consumption']);
			$female_specie->set_monthly_water_consumption($species_config['attributes']['monthly_water_consumption']);
			$female_specie->set_minimum_temperature($species_config['attributes']['minimum_temperature']);
			$female_specie->set_maximum_temperature($species_config['attributes']['maximum_temperature']);

			$habitat->attach($male_specie);
			$habitat->attach($female_specie);
			for($j=0; $j<$runtime_months; $j++){
				$habitat->init();
				$habitat->notify();
				$current_population = $habitat->get_population();
				$total_habitat_population += $current_population;
				if($current_population > $max_habitat_population){
					$max_habitat_population = $current_population;
				}
			}
		}
		debug('max population:' . $max_habitat_population);
		debug('average population:' . ceil($total_habitat_population / ($runtime_months * $runtime_iterations)));
	}
}

?>