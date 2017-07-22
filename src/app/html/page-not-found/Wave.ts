import { Waves } from './Waves';

export class Wave {

	ctx: any;
	force: any;
	wavePower: any;
	count: any;
	y: any;
	$y: any;
	alpha: any;
	width: any;
	colour: any;

	constructor( $canvas, $y, $colour ) {

		this.ctx = $canvas.getContext( '2d' );
		this.force = 0;
		this.wavePower = 40;
		this.count = $y;
		this.$y = $y;
		this.y = $y + Waves.globalY;
		this.alpha = 0.1;
		this.colour = $colour;
	}

	public update() {

		this.y = this.$y + Waves.globalY;
		this.force = Math.sin(this.count);
		this.count += 0.05;
	}

	public draw() {

		this.ctx.fillStyle = "rgba(0, 0, 0, 0.1)";
		this.ctx.fillRect(0,0,Waves.width,Waves.height);
		this.ctx.fillStyle = "rgba("+this.colour+", "+this.alpha+")";
		this.ctx.beginPath();
		this.ctx.moveTo(0, this.y);
		this.ctx.quadraticCurveTo(Waves.width / 4, this.y + ( this.wavePower * this.force ), Waves.width / 2, this.y);
		this.ctx.quadraticCurveTo(Waves.width * 0.75, this.y - ( this.wavePower * this.force ), Waves.width, this.y);
		this.ctx.lineTo(Waves.width, Waves.height);
		this.ctx.lineTo(0, Waves.height);
		this.ctx.lineTo(0, this.y);
		this.ctx.closePath();
		this.ctx.fill();
	}
}