/**
* 404 component.
* @since   1.0.0 <2017-05-15>
*/
import { Component, OnInit, OnChanges, OnDestroy } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { PageNotFound } from "./page-not-found";

@Component({
	selector: 'page-not-found',
	templateUrl: './page-not-found.html'
})
export class PageNotFoundComponent implements OnInit, OnChanges, OnDestroy {

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
