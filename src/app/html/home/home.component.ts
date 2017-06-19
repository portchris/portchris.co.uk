/**
* Homepage component.
* @since   1.0.0 <2017-05-15>
*/
import { Component, AfterViewInit, OnChanges, OnDestroy, ViewChild } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Home } from './home';
import { MessagesComponent } from '../../messages/messages.component';

@Component({
	selector: 'home',
	templateUrl: './home.html'
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
	constructor(private route: ActivatedRoute) { 

		this.router = route;
	}

	ngAfterViewInit() {
		console.log(this.messages);
		this.sub = this.router.data.subscribe((v) => {
			this.storage = v.storage;
			this.messages.setStorage(v.storage);
		});
	}

	ngOnChanges() {

	}

	ngOnDestroy() {

		this.sub.unsubscribe();
	}
}
