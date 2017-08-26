/**
* This component creates the skeleton layout of the page using bootstrap grid system.
* @since   1.0.0 <2017-05-15>
*/
import { NgModule } from '@angular/core';
import { Component, OnInit, OnChanges, OnDestroy } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { ColumnLayout } from "./column-layout";
import { HeaderComponent } from "./header/header.component";
import { SidebarComponent } from "./sidebar/sidebar.component";

// @NgModule({
// 	declarations: [ 
// 		HeaderComponent,
// 		SidebarComponent
// 	]
// })

@Component({
	selector: 'app-column-layout',
	templateUrl: './column-layout.html'
})
export class ColumnLayoutComponent implements OnInit, OnChanges, OnDestroy {
	
	sub: any;
	router: any;
	columns: any;
	errMesg: any;

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
			this.columns = v.columns;
		});	
	}

	ngOnChanges() {

	}

	ngOnDestroy() {

		this.sub.unsubscribe();
	}
}
