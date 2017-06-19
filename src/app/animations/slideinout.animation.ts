/**
* @author 	Chris Rogers
* @since 	2016-06-18
* @see 		http://jasonwatmore.com/post/2017/04/19/angular-2-4-router-animation-tutorial-example
*/

// Import the required animation functions from the angular animations module
import { trigger, state, animate, transition, style } from '@angular/animations';

export const slideInOutAnimation =

// Trigger name for attaching this animation to an element using the [@triggerName] syntax
trigger('slideInOutAnimation', [

	// End state styles for route container (host)
	state('*', style({
		position: 'absolute',
		// display: 'inline-block',
		transform: 'translateX(0)',
		transition: '0.2s 100ms ease-out',
		opacity: '1',
		width: "100%",
		height: "100%"
	})),

	// Route 'enter' transition
	transition(':enter', [

		// Styles at start of transition
		style({

			// Start with the content positioned off the right of the screen,
			transform: 'translateX(150%)',
			transition: '0.2s 100ms ease-out',

			// Start with background opacity set to 0 (invisible)
			opacity: '0'
		}),

		// Animation and styles at end of transition
		animate('.5s ease-in-out', style({
			
			// Transition the right position to 0 which slides the content into view
			transform: 'translateX(0)',
			transition: '0.2s 100ms ease-out',

			// Transition the background opacity to 0.8 to fade it in
			opacity: '1'
		}))
	]),

	// Route 'leave' transition
	transition(':leave', [
		
		// Animation and styles at end of transition
		animate('.5s ease-in-out', style({
			
			// Transition the right position to -400% which slides the content out of view
			transform: 'translateX(150%)',
			transition: '0.2s 100ms ease-out',
			
			// Transition the background opacity to 0 to fade it out
			opacity: '0'
		}))
	])
]);