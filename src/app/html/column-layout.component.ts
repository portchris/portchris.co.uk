/**
* This component creates the skeleton layout of the page using bootstrap grid system.
* @since   1.0.0 <2017-05-15>
*/
import { Component, OnInit } from '@angular/core';
import * as ColumnLayout from "./column-layout";
import { PageNotFoundComponent } from "./page-not-found/page-not-found.component";

@Component({
	selector: 'column-layout',
	templateUrl: './column-layout.html',
	// styleUrls: ['./column-layout.css']
	// directives: [
	// 	PageNotFoundComponent
	// ]
})
export class ColumnLayoutComponent implements OnInit {
	
	columns: any;
	errMesg: any;

	/**
	* Must pass in the data object for the columns to work. 
	* Accepts columns.class, columns.content
	* @param  JSON   data
	*/
	constructor(public data) { 

		this.columns = data.columns;
	}

	ngOnInit() {
		
	}
}
