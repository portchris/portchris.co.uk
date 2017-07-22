import { Wave } from './Wave';

export class Waves {

	numberOfWaves: any;
	waveGap: any;
	width: any;
	height: any;
	move: any;
	ctx: any;
	colour: any;
	wavesArray: any;
	beginingY: any;

	public static globalY;
	public static width;
	public static height;

	constructor( $canvas, $width, $height ) {
		
		this.numberOfWaves = 10;
		this.waveGap = 20;
		this.width = Waves.width = $width;
		this.height = Waves.height = $height;
		Waves.globalY = 0;
		this.move = 1;
		this.ctx = $canvas.getContext( '2d' );

		this.colour = Math.round(Math.random()*255)+", "+Math.round(Math.random()*255)+", "+Math.round(Math.random()*255);

		this.wavesArray = new Array();

		this.beginingY = Waves.height / 2;
		while(this.numberOfWaves--){
			this.wavesArray.push(new Wave($canvas, this.beginingY, this.colour));
			this.beginingY += this.waveGap;
		}
	}

	public update() {

		var bL = this.wavesArray.length;
		while( bL-- ){
			this.wavesArray[ bL ].update( );
		}
		Waves.globalY += this.move;
		if(Waves.globalY > (Waves.height / 2)-50){
			this.move = -1;
		}else if(Waves.globalY < -(Waves.height / 2)){
			this.move = 1;
		}
	}

	public draw() {

		this.ctx.save();
		var bL = this.wavesArray.length;
		while( bL-- ){
			this.wavesArray[ bL ].draw( );
		}
		this.ctx.restore();
	}
}