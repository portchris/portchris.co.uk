/**
* Unit test for the Planner component
*
* @author 	Chris Rogers
* @since 	<1.5.0> 2020-11-26
*
* tslint:disable:no-unused-variable 
*/
import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { DebugElement } from '@angular/core';

import { PlannerComponent } from './planner.component';

describe('PlannerComponent', () => {
	let component: PlannerComponent;
	let fixture: ComponentFixture<PlannerComponent>;

	beforeEach(async(() => {
		TestBed.configureTestingModule({
			declarations: [ PlannerComponent ]
		})
		.compileComponents();
	}));

	beforeEach(() => {
		fixture = TestBed.createComponent(PlannerComponent);
		component = fixture.componentInstance;
		fixture.detectChanges();
	});

	it('should create', () => {
		expect(component).toBeTruthy();
	});
});
