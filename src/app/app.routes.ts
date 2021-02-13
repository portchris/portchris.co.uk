import { NgModule } from '@angular/core';
import { ModuleWithProviders } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule } from '@angular/forms';
import { HomeComponent } from './html/home/home.component';
import { ContactComponent } from './html/contact/contact.component';
import { PageNotFoundComponent } from './html/page-not-found/page-not-found.component';
import { PortfolioComponent } from './html/portfolio/portfolio.component';
import { ColumnLayoutComponent } from './html/column-layout.component';
import { ImportStoryComponent } from './import/import.story.component';
import { ResourcePlannerComponent } from './html/resource-planner/resource-planner.component';

const Router: Routes = [
	{
		path: "",
		component: ColumnLayoutComponent,
		data: {
			columns: [
				{
					class: "col-md-3 header"
				},
				{
					class: "col-md-7 main"
				},
				{
					class: "col-md-2 sidebar"
				}
			]
		},
		children: [
			{
				path: "",
				component: HomeComponent
			},
			{
				path: "portfolio",
				component: PortfolioComponent
			},
			{
				path: "contact",
				component: ContactComponent
			},
			{
				path: "resource-planner",
				component: ResourcePlannerComponent
			},
			{
				path: "import/:id",
				component: ImportStoryComponent
			},
			{
				path: '**',
				component: PageNotFoundComponent
			}
		]
	}
];
// const AppRoutes: ModuleWithProviders = RouterModule.forRoot(Router, { relativeLinkResolution: 'legacy' });
@NgModule({
	imports: [
		BrowserModule,
		RouterModule.forRoot(Router),
		FormsModule
	],
	exports: [
		RouterModule
	]
})
export class AppRoutingModule { }