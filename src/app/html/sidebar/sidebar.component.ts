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
			(success) => { this.adminUser = success; },
			(error) => { console.error(error); }
		);
		if (this.adminUser && this.adminUser.lat && this.adminUser.lng) {
			this.weatherService.getWeatherByCoordinates(this.adminUser.lat, this.adminUser.lng).subscribe(
				(success) => { this.weather = success; },
				(error) => { this.hideWeatherPanel(); console.error(error); }
			);
		} else {
			this.hideWeatherPanel();
		}
	}

	private hideWeatherPanel() {

		this.weatherPanel.nativeElement.classList.add("hidden");
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
