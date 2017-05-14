/**
* Unit test for the messages component
*
* @author 	Chris Rogers
* @since 	2017-05-14
*
* tslint:disable:no-unused-variable 
*/
import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { DebugElement } from '@angular/core';

import { MessagesComponent } from './messages.component';

describe('MessagesComponent', () => {
	let component: MessagesComponent;
	let fixture: ComponentFixture<MessagesComponent>;

	beforeEach(async(() => {
		TestBed.configureTestingModule({
			declarations: [ MessagesComponent ]
		})
		.compileComponents();
	}));

	beforeEach(() => {
		fixture = TestBed.createComponent(MessagesComponent);
		component = fixture.componentInstance;
		fixture.detectChanges();
	});

	it('should create', () => {
		expect(component).toBeTruthy();
	});
});
