/**
* This is a request validator unit test using ES6 promises
*
* @author 	Chris Rogers
* @since 	2017-05-14
*
* tslint:disable:no-unused-variable 
*/

import { TestBed, async, inject } from '@angular/core/testing';
import { ContactService } from './contact.service';

describe('ContactService', () => {
	
	beforeEach(() => {
		TestBed.configureTestingModule({
			providers: [ContactService]
		});
	});

	it('should ...', inject([ContactService], (service: ContactService) => {
		expect(service).toBeTruthy();
	}));
});
