/**
* This is a request validator unit test using ES6 promises
*
* @author 	Chris Rogers
* @since 	2017-05-14
*
* tslint:disable:no-unused-variable 
*/

import { TestBed, inject, waitForAsync } from '@angular/core/testing';
import { UserService } from './user.service';

describe('UserService', () => {
	
	beforeEach(() => {
		TestBed.configureTestingModule({
			providers: [UserService]
		});
	});

	it('should ...', inject([UserService], (service: UserService) => {
		expect(service).toBeTruthy();
	}));
});
