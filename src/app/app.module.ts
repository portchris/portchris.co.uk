import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { Http, HttpModule } from '@angular/http';
import { RouterModule, Routes } from '@angular/router';

import { AppComponent } from './app.component';
import { BookComponent } from './book/book.component';
import { BookService } from "./book/book.service";
import { MessagesComponent } from './messages/messages.component';
import { MessagesService } from "./messages/messages.service";
import { UserComponent } from "./user/user.component";
import { UserService } from "./user/user.service";
import { ColumnLayoutComponent } from "./html/column-layout.component";
import { PageNotFoundComponent } from "./html/page-not-found/page-not-found.component";
import { ParticlesComponent } from "./particles/particles.component";


const appRoutes: Routes = [
// {
// 	path: '/',
// 	component: ColumnLayoutComponent,
// 	data: {
// 		columns: [{
// 			width: "col-md-12",
// 			content: "Blah"
// 		}]
// 	}
// }, 
{ 
	path: 'portfolio',
	component: ColumnLayoutComponent,
	data: {
		title: 'About myself and my portfolio'
	}
}, { 
	path: 'contact' 
},
{ 
	path: '**', 
	component: PageNotFoundComponent 
}];

@NgModule({
	declarations: [
		AppComponent,
		BookComponent,
		UserComponent,
		MessagesComponent,
		ParticlesComponent
	],
	imports: [
		BrowserModule,
		ReactiveFormsModule,
		FormsModule,
		HttpModule,
		// RouterModule.forRoot(appRoutes)
	],
	providers: [
		BookService, 
		UserService, 
		MessagesService
	],
	bootstrap: [
		AppComponent
	]
})
export class AppModule { 
	
	url: string;
	
	constructor() {

		this.url = "http://portchris.hades.portchris.net:8081/";
	}

	ngOnInit() {

		this.getUser();
	}

	/**
	* Attempt to identify the user via session in order to retrieve their previous conversations. 
	* Else require them to login and assign as guest
	*/
	getUser() {

		// var User = new UserService(new Http()).getUser();
	}
}
