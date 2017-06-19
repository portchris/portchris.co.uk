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
		// The view covers the whole screen with a semi tranparent background
		position: 'fixed',
		top: 0,
		left: 0,
		right: 0,
		bottom: 0,
		backgroundColor: 'rgba(0, 0, 0, 0.8)'
	})),

	// Route 'enter' transition
	transition(':enter', [

		// Styles at start of transition
		style({

			// Start with the content positioned off the right of the screen,
			// -400% is required instead of -100% because the negative position adds to the width of the element
			right: '-400%',

			// Start with background opacity set to 0 (invisible)
			backgroundColor: 'rgba(0, 0, 0, 0)'
		}),

		// Animation and styles at end of transition
		animate('.5s ease-in-out', style({
			
			// Transition the right position to 0 which slides the content into view
			right: 0,

			// Transition the background opacity to 0.8 to fade it in
			backgroundColor: 'rgba(0, 0, 0, 0.8)'
		}))
	]),

	// Route 'leave' transition
	transition(':leave', [
		
		// Animation and styles at end of transition
		animate('.5s ease-in-out', style({
			
			// Transition the right position to -400% which slides the content out of view
			right: '-400%',

			// Transition the background opacity to 0 to fade it out
			backgroundColor: 'rgba(0, 0, 0, 0)'
		}))
	])
]);