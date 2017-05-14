import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpModule } from '@angular/http';

import { AppComponent } from './app.component';
import { BookComponent } from './book/book.component';
import { BookService } from "./book/book.service";
import { MessagesComponent } from './messages/messages.component';
import { MessagesService } from "./messages/messages.service";

@NgModule({
	declarations: [
		AppComponent,
		BookComponent,
		MessagesComponent
	],
	imports: [
		BrowserModule,
		FormsModule,
		HttpModule
	],
	providers: [BookService, MessagesService],
	bootstrap: [AppComponent]
})
export class AppModule { 
	
	url: string;
	
	constructor() {
		this.url = "http://portchris.hades.portchris.net:8081/";
	}
}
