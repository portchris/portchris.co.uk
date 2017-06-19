/**
* Contact me page component.
* @since   1.0.0 <2017-05-15>
*/
import { Component, OnInit, OnChanges } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Contact } from "./contact";

@Component({
	selector: 'contact',
	templateUrl: './contact.html'
})
export class ContactComponent implements OnInit, OnChanges {

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

	ngOnInit() {
	
		this.router.snapshot.params['messages'];
		this.sub = this.router.data.subscribe((v) => {
			console.log(v);
		});
	}

	ngOnChanges() {

	}

	ngOnDestroy() {

		this.sub.unsubscribe();
	}
}
