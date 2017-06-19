import { NgModule } from '@angular/core';
import { ModuleWithProviders } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { HomeComponent } from "./html/home/home.component";
import { ContactComponent } from "./html/contact/contact.component";
import { PageNotFoundComponent } from "./html/page-not-found/page-not-found.component";
import { PortfolioComponent } from "./html/portfolio/portfolio.component";
import { ColumnLayoutComponent } from "./html/column-layout.component";

const Router: Routes = [
	{
		path: "",
		component: ColumnLayoutComponent,
		data: {
			columns: [
				{
					width: "col-md-3",
					content: ""
				},
				{
					width: "col-md-9",
					content: ""
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
				path: '**', 
				component: PageNotFoundComponent
			}
		]
	}
];
const AppRoutes: ModuleWithProviders = RouterModule.forRoot(Router);
@NgModule({
  imports: [ AppRoutes ],
  exports: [ RouterModule ]
})
export class AppRoutingModule {}