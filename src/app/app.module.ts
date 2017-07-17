import { BrowserModule } from '@angular/platform-browser';
import { NgModule, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { Http, HttpModule } from '@angular/http';
import { RouterModule } from '@angular/router';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { AppRoutingModule } from './app.routes';
import { AppComponent } from './app.component';
import { DataStorageService } from './app.storage.service';
import { MessagesComponent } from './messages/messages.component';
import { MessagesService } from "./messages/messages.service";
import { UserComponent } from "./user/user.component";
import { ParticlesComponent } from "./particles/particles.component";
import { HeaderComponent } from "./html/header/header.component";
import { SidebarComponent } from "./html/sidebar/sidebar.component";
import { HomeComponent } from "./html/home/home.component";
import { ColumnLayoutComponent } from "./html/column-layout.component";
import { ContactComponent } from "./html/contact/contact.component";
import { PageNotFoundComponent } from "./html/page-not-found/page-not-found.component";
import { PortfolioComponent } from "./html/portfolio/portfolio.component";
import { ImportStoryComponent } from "./import/import.story.component";

@NgModule({
	declarations: [
		AppComponent,
		UserComponent,
		MessagesComponent,
		ParticlesComponent,
		HomeComponent,
		HeaderComponent,
		SidebarComponent,
		ColumnLayoutComponent,
		ContactComponent,
		PageNotFoundComponent,
		PortfolioComponent,
		ImportStoryComponent
	],
	imports: [
		BrowserModule,
		ReactiveFormsModule,
		FormsModule,
		HttpModule,
		AppRoutingModule,
		BrowserAnimationsModule
	],
	providers: [
		MessagesService,
		DataStorageService
	],
	bootstrap: [
		AppComponent
	],
	schemas: [
		CUSTOM_ELEMENTS_SCHEMA
	]
})
export class AppModule { 
	
	url: string;
	
	constructor() {

		this.url = "http://portchris.hades.portchris.net:8081/";
	}

	ngOnInit() {

	}
}
