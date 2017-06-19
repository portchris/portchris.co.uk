/**
* Header component.
* @since   1.0.0 <2017-05-15>
*/
import { Component, OnInit, OnChanges } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Header } from "./header";

@Component({
	selector: 'app-header',
	templateUrl: './header.html'
})
export class HeaderComponent implements OnInit, OnChanges {

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
