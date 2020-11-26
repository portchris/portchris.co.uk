/**
* This is a request validator unit test using ES6 promises
*
* @author 	Chris Rogers
* @since 	<1.5.0> 2020-11-26
*
* tslint:disable:no-unused-variable 
*/

import { TestBed, async, inject } from '@angular/core/testing';
import { PlannerService } from './planner.service';

describe('PlannerService', () => {
	
	beforeEach(() => {
		TestBed.configureTestingModule({
			providers: [PlannerService]
		});
	});

	it('should ...', inject([PlannerService], (service: PlannerService) => {
		expect(service).toBeTruthy();
	}));
});
