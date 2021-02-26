/* eslint-disable , , , , , , , , , , , , ,  */

import { TestBed, inject, waitForAsync } from '@angular/core/testing';
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
