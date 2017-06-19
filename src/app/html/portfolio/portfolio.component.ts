/**
* Portfolio / about component.
* @since   1.0.0 <2017-05-15>
*/
import { Component, OnInit, OnChanges, OnDestroy } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Portfolio } from "./portfolio";

@Component({
	selector: 'portfolio',
	templateUrl: './portfolio.html'
})
export class PortfolioComponent implements OnInit, OnChanges, OnDestroy {

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
