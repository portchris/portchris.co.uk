/**
* @author 	Chris Rogers
* @since 	2016-06-18
* @see 		http://jasonwatmore.com/post/2017/04/19/angular-2-4-router-animation-tutorial-example
*/

// Import the required animation functions from the angular animations module
import { trigger, state, animate, transition, style } from '@angular/animations';

export const fadeInAnimation =

// Trigger name for attaching this animation to an element using the [@triggerName] syntax
trigger('fadeInAnimation', [

	// Route 'enter' transition
	transition(':enter', [

		// CSS styles at start of transition
		style({ opacity: 0 }),

		// Animation and styles at end of transition
		animate('.3s', style({ opacity: 1 }))
	]),
]);