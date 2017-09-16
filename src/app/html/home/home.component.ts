/**
* Homepage component.
* @since   1.0.0 <2017-05-15>
*/
import { Component, AfterViewInit, OnChanges, OnDestroy, ViewChild } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Home } from './home';
import { MessagesComponent } from '../../messages/messages.component';
import { slideInOutAnimation } from '../../animations/slideinout.animation';

@Component({
	selector: 'home',
	templateUrl: './home.html',
	animations: [slideInOutAnimation], // Make  animation available to this component
	host: { '[@slideInOutAnimation]': '' } // Attach the fade in animation to the host (root) element of this component
})
export class HomeComponent implements AfterViewInit, OnChanges, OnDestroy {

	@ViewChild("childComponent") messages: MessagesComponent;

	storage: any;
	router: any;
	sub: any;

	/**
	* Must pass in the data object for the columns to work. 
	* Accepts columns.class, columns.content
	* @param  ActivatedRoute   route
	*/
	public constructor(private route: ActivatedRoute) { 

		this.router = route;
	}

	/**
	* After view initialises
	*/
	public ngAfterViewInit() {

		this.sub = this.router.data.subscribe((v) => { this.subscriber(v) });
	}

	/**
	* When view updates
	*/
	public ngOnChanges() {

	}

	/**
	* When view is destroyed
	*/
	public ngOnDestroy() {

		this.sub.unsubscribe();
	}

	/**
	* Router information observable
	* @param 	Observable 	v
	*/
	public subscriber(v) {

		// if (window.hasOwnProperty('ga')) {
		// 	window.ga('set', 'page', v.url);
		// 	window.ga('send', 'pageview');
		// }
	}
}
