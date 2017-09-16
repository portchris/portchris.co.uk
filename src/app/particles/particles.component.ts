import { Component, OnInit } from '@angular/core';
import { Particles } from './particles';
declare var particlesJS: any;

@Component({
	selector: 'app-particles',
	templateUrl: './particles.component.html',
	styleUrls: ['./particles.component.css']
})
export class ParticlesComponent implements OnInit {

	particles: any;
	errMesg: any;
	settings: any;

	constructor() {

		this.settings = {
		"particles": {
			"number": {
				"value": 10,
				"density": {
					"enable": true,
					"value_area": 200
				}
			},
			"color": {
				"value": "#ffffff"
			},
			"shape": {
				"type": "circle",
				"stroke": {
					"width": 2,
					"color": "#252733"
				},
				"polygon": {
					"nb_sides": 5
				}
			},
			"opacity": {
				"value": 0.5,
				"random": false,
				"anim": {
					"enable": false,
					"speed": 0.25,
					"opacity_min": 0.1,
					"sync": false
				}
			},
			"size": {
				"value": 2,
				"random": true,
				"anim": {
					"enable": false,
					"speed": 10,
					"size_min": 0.1,
					"sync": false
				}
			},
			"line_linked": {
				"enable": false,
				"distance": 1000,
				"color": "#ffffff",
				"opacity": 0.4,
				"width": 1
			},
			"move": {
				"enable": true,
				"speed": 1,
				"direction": "none",
				"random": false,
				"straight": false,
				"out_mode": "out",
				"attract": {
					"enable": false,
					"rotateX": 600,
					"rotateY": 1200
				}
			}
		},
		"interactivity": {
			"detect_on": "canvas",
			"events": {
				"onhover": {
					"enable": true,
					"mode": "bubble"
				},
				"onclick": {
					"enable": false,
					"mode": "push"
				},
				"resize": false
			},
			"modes": {
				"grab": {
					"distance": 400,
					"line_linked": {
						"opacity": 1
					}
				},
				"bubble": {
					"distance": 1000,
					"size": 1,
					"duration": 100,
					"opacity": 0.25,
					"speed": 1
				},
				"repulse": {
					"distance": 10,
					"duration": 10
				},
				"push": {
					"particles_nb": 4
				},
				"remove": {
					"particles_nb": 2
				}
			}
		},
		"retina_detect": true,
		"config_demo": {
			"hide_card": false,
			"background_color": "#b61924",
			"background_image": "",
			"background_position": "50% 50%",
			"background_repeat": "no-repeat",
			"background_size": "cover"
		}
	};
	}

	ngOnInit() {

		// console.log(this.settings);
		particlesJS(this.settings);
	}
}
