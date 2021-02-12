import { BrowserModule } from '@angular/platform-browser';
import { NgModule, CUSTOM_ELEMENTS_SCHEMA, Directive } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { Http, HttpModule, JsonpModule } from '@angular/http';
import { RouterModule } from '@angular/router';
import { isDevMode } from '@angular/core';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { AppRoutingModule } from './app.routes';
import { AppComponent } from './app.component';
import { DataStorageService } from './app.storage.service';
import { MessagesComponent } from './messages/messages.component';
import { MessagesService } from './messages/messages.service';
import { UserComponent } from './user/user.component';
import { ParticlesComponent } from './particles/particles.component';
import { HeaderComponent } from './html/header/header.component';
import { SidebarComponent } from './html/sidebar/sidebar.component';
import { HomeComponent } from './html/home/home.component';
import { ColumnLayoutComponent } from './html/column-layout.component';
import { ContactComponent } from './html/contact/contact.component';
import { ContactService } from './html/contact/contact.service';
import { PageNotFoundComponent } from './html/page-not-found/page-not-found.component';
import { PortfolioComponent } from './html/portfolio/portfolio.component';
import { ImportStoryComponent } from './import/import.story.component';
import { ImportStoryService } from './import/import.story.service';
import { WeatherService } from './html/sidebar/weather.service';
import { ResourcePlannerComponent } from './html/resource-planner/resource-planner.component';
import { PlannerComponent } from './planner/planner.component';
import { PlannerService } from './planner/planner.service';

@Directive()
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
		ImportStoryComponent,
		ResourcePlannerComponent,
		PlannerComponent
	],
	imports: [
		BrowserModule,
		ReactiveFormsModule,
		FormsModule,
		HttpModule,
		JsonpModule,
		AppRoutingModule,
		BrowserAnimationsModule
	],
	providers: [
		MessagesService,
		DataStorageService,
		WeatherService,
		ContactService,
		ImportStoryService,
		PlannerService
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

		// I'm sorry I had to do it!
		this.url = (isDevMode) ? "http://api.portchris.localhost/" : "https://api.portchris.co.uk/";
	}

	ngOnInit() {

	}
}
