/**
* Sidebar component.
* @since   1.0.0 <2017-05-15>
*/
import { Component, OnInit, OnChanges, ViewChild, ElementRef } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Sidebar } from "./sidebar";
import { MessagesComponent } from '../../messages/messages.component';
import { MessagesService } from '../../messages/messages.service';
import { WeatherService } from './weather.service';

@Component({
	selector: 'app-sidebar',
	templateUrl: './sidebar.html',
	styleUrls: ["sidebar.component.css"]
})
export class SidebarComponent implements OnInit, OnChanges {

	/**
	* Refernce to a child element
	*/
	@ViewChild('weatherPanel') private weatherPanel: ElementRef;
	@ViewChild('helpPanel') private helpPanel: ElementRef;
	@ViewChild('cvPanel') private cvPanel: ElementRef;

	router: any;
	sub: any;
	help: any;
	weather: any;
	timezone: any;
	adminUser: any;

	/**
	* Must pass in the data object for the columns to work. 
	* Accepts columns.class, columns.content
	* @param  ActivatedRoute   route
	*/
	constructor(private route: ActivatedRoute, private weatherService: WeatherService, private messagesService: MessagesService) { 

		this.router = route;
		this.help = [];
	}

	public ngOnInit() {
	
		this.sub = this.router.data.subscribe((v) => {
			console.log(v);
		});	
		this.createHelpList();
		this.messagesService.getAdminUser().subscribe(
			(success) => { 
				this.adminUser = success; 
				if (this.adminUser && this.adminUser.lat && this.adminUser.lng) {
					this.weatherService.getWeatherByCoordinates(this.adminUser.lat, this.adminUser.lng).subscribe(
						(w) => { 
							if (w.hasOwnProperty("weather") && w.hasOwnProperty("name") && w.hasOwnProperty("main")) {
								this.weatherService.getTimezoneByCoordinates(this.adminUser.lat, this.adminUser.lng).subscribe(
									(t) => {
										this.displayWeatherPanel(w, t);
									},
									(error) => {
										this.hideWeatherPanel();
										console.error(error);
									}
								); 
							}
						},
						(error) => { 
							this.hideWeatherPanel(); 
							console.error(error); 
						}
					);
				} else {
					this.hideWeatherPanel();
				}
			},
			(error) => { 
				console.error(error); 
			}
		);
	}

	/**
	* In case the weather info breaks, hide the panel
	*/
	private hideWeatherPanel() {

		this.weatherPanel.nativeElement.classList.add("hidden");
	}

	/**
	* Set the weather object for the weather panel to display results
	* @param 	w 	object
	* @param 	t 	object
	*/
	private displayWeatherPanel(w: any, t: any) {

		if (w && t && w.weather && w.name && w.main && t.time) {
			let weatherCond = w.weather[0];
			this.weather = w;
			this.timezone = t;
			this.displayWeatherIcon(weatherCond.main, weatherCond.description);
		}
	}

	/**
	* Map the Open Weather Map (OWM) icon name with the SVG icon name
	* @param 	owmIcon 	string
	* @param 	owmDesc 	string
	*/
	private displayWeatherIcon(owmIcon: string, owmDesc) {

		if (this.weather) {
			let r = '';
			let icon = owmIcon.toLowerCase();
			let desc = owmDesc.toLowerCase().replace(/ /g, "");
			switch (icon) {
				case "thunderstorm":
					this.weather.icon = "#flash";
				break;
				case "drizzle":
				case "rain":
					this.weather.icon = "#raining";
				break;
				case "snow":
					this.weather.icon = "#snowflake";
				break;
				case "atmosphere":
					this.weather.icon = "#wind";
				break;
				case "clear":
					this.weather.icon = "#sun-sunny-day-weather-symbol";
				break;
				case "clouds":
					switch (desc) {
						case "fewclouds":
						case "scatteredclouds":
							this.weather.icon = "#cloudy-day-outlined-weather-interface-symbol";
						break;
						case "brokenclouds":
						case "overcastclouds":
							this.weather.icon = "#cloud-outline";
						break;
						default:
							this.weather.icon = "#cloudy-day-outlined-weather-interface-symbol";
						break;
					}
				break;
				case "extreme":
					this.weather.icon = "#wind";
				break;
				default:
					this.weather.icon = "#cloud-outline";
				break;
			}
		}
	}

	/**
	* Show hide content in sidebar panels
	* @param 	object 	event
	* @param 	boolean 	isChild
	*/
	private changeShowStatus(event, isChild = false) {

		let target = event.target.offsetParent || event.srcElement.offsetParent || event.currentTarget.offsetParent;
		event.preventDefault();
		target.classList.toggle("show");
	}

	/**
	* Compile list of helper functions for text based adventure using MessagesComponents magic words list
	*/
	private createHelpList() {

		MessagesComponent.MAGIC_WORDS.forEach((value, index, words) => {
			this.help.push(value.phrase);
		});
	}

	public ngOnChanges() {

	}

	public ngOnDestroy() {

		this.sub.unsubscribe();
	}
}
