/**
* @author 	Chris Rogers
* @since 	2016-06-18
* @see 		http://jasonwatmore.com/post/2017/04/19/angular-2-4-router-animation-tutorial-example
*/
import { trigger, state, animate, transition, style } from '@angular/animations';

export const slideUpAnimation = trigger('slideUpAnimation', [
	state('*', style({
		transform: 'translateY(0)',
		transition: '0.2s 500ms ease-in-out',
		opacity: '1'
	})),
	transition(':enter', [
		style({
			transform: 'translateY(150%)',
			transition: '0.2s 500ms ease-in-out',
			opacity: '0'
		}),
		animate('.5s ease-in-out', style({
			transform: 'translateY(0)',
			transition: '0.2s 500ms ease-in-out',
			opacity: '1'
		}))
	]),
	transition(':leave', [
		animate('.5s ease-in-out', style({
			transform: 'translateY(150%)',
			transition: '0.2s 100ms ease-in-out',
			opacity: '0'
		}))
	])
]);