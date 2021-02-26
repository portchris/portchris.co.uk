/* eslint-disable , , , , , , , , , , , , , ,  */

import { TestBed, inject, waitForAsync } from '@angular/core/testing';
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
