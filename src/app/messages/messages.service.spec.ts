/**
* This is a request validator unit test using ES6 promises
*
* @author 	Chris Rogers
* @since 	2017-05-14
*
* tslint:disable:no-unused-variable 
*/

import { TestBed, async, inject } from '@angular/core/testing';
import { MessagesService } from './messages.service';

describe('MessagesService', () => {
	
	beforeEach(() => {
		TestBed.configureTestingModule({
			providers: [MessagesService]
		});
	});

	it('should ...', inject([MessagesService], (service: MessagesService) => {
		expect(service).toBeTruthy();
	}));
});
