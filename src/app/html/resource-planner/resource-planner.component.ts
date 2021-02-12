/**
* Resrouce Planner component.
* @since   1.5.0 <2020-11-26>
*/
import { Component, AfterViewInit, OnChanges, OnDestroy, ViewChild } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Planner } from '../../planner/planner';
import { slideInOutAnimation } from '../../animations/slideinout.animation';

@Component({
    selector: 'resource-planner',
    templateUrl: './resource-planner.html',
    animations: [slideInOutAnimation], // Make  animation available to this component
    host: { '[@slideInOutAnimation]': '' } // Attach the fade in animation to the host (root) element of this component
})
export class ResourcePlannerComponent implements AfterViewInit, OnChanges, OnDestroy {

    @ViewChild("googleSheetsComponent") planner: Planner;

    storage: any;
    router: any;
    sub: any;

    /**
     * Must pass in the data object for the columns to work. 
     * Accepts columns.class, columns.content
     * @param  ActivatedRoute   route
     */
    public constructor(private route: ActivatedRoute) {

        this.router = route;
    }

    /**
     * After view initialises
     */
    public ngAfterViewInit() {

        this.sub = this.router.data.subscribe((v) => { this.subscriber(v) });
    }

    /**
     * When view updates
     */
    public ngOnChanges() {

    }

    /**
     * When view is destroyed
     */
    public ngOnDestroy() {

        this.sub.unsubscribe();
    }

    /**
     * Router information observable
     * @param 	Observable 	v
     */
    public subscriber(v) {

        // if (window.hasOwnProperty('ga')) {
        // 	window.ga('set', 'page', v.url);
        // 	window.ga('send', 'pageview');
        // }
    }
}
