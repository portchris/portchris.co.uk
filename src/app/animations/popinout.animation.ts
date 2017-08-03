/**
* @author 	Chris Rogers
* @since 	2016-06-18
* @see 		http://jasonwatmore.com/post/2017/04/19/angular-2-4-router-animation-tutorial-example
*/

// Import the required animation functions from the angular animations module
import { trigger, state, animate, transition, style } from '@angular/animations';

export const popInOutAnimation =

// Trigger name for attaching this animation to an element using the [@triggerName] syntax
trigger('popInOutAnimation', [

	// End state styles for route container (host)
	state('*', style({
		position: 'absolute',
		transform: 'translateY(0)',
		transition: '0.2s 500ms ease-in-out',
		opacity: '1',
	})),

	// Route 'enter' transition
	transition(':enter', [

		// Styles at start of transition
		style({

			// Start with the content positioned off the right of the screen,
			transform: 'translateY(150%)',
			transition: '0.2s 500ms ease-in-out',

			// Start with background opacity set to 0 (invisible)
			opacity: '0'
		}),

		// Animation and styles at end of transition
		animate('.5s ease-in-out', style({
			
			// Transition the right position to 0 which slides the content into view
			transform: 'translateY(0)',
			transition: '0.2s 500ms ease-in-out',

			// Transition the background opacity to 0.8 to fade it in
			opacity: '1'
		}))
	]),

	// Route 'leave' transition
	transition(':leave', [
		
		// Animation and styles at end of transition
		animate('.5s ease-in-out', style({
			
			// Transition the right position to -400% which slides the content out of view
			transform: 'translateY(150%)',
			transition: '0.2s 100ms ease-in-out',
			
			// Transition the background opacity to 0 to fade it out
			opacity: '0'
		}))
	])
]);