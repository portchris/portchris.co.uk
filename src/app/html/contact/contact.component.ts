/**
* Contact me page component.
* @since   1.0.0 <2017-05-15>
*/
import { Component, OnInit, OnChanges } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Contact } from "./contact";
import { ContactService } from "./contact.service";
import { slideInOutAnimation } from '../../animations/slideinout.animation';
import { popInOutAnimation } from '../../animations/popinout.animation';
import { FormBuilder, Validators } from '@angular/forms';
import { Observable }  from 'rxjs/Observable';
import 'rxjs/add/operator/debounceTime';

@Component({
	selector: 'contact',
	templateUrl: './contact.component.html',
	styleUrls: ['./contact.component.css'],
	animations: [slideInOutAnimation, popInOutAnimation],
	host: { '[@slideInOutAnimation]': '' },
	providers: [ContactService]
})
export class ContactComponent implements OnInit, OnChanges {

	/**
	* General component error message that will appear
	* @var 	string
	*/
	public err: string;

	/**
	* General component success message that will appear
	* @var 	string
	*/
	public success: string;

	/**
	* Route information from parent
	* @var 	ActivatedRoute
	*/
	private router: any;
	
	/**
	* Subscriber to the router 
	* @var 	Observable
	*/
	private sub: any;

	/**
	* If the contact service is sending
	* @var 	boolean
	*/
	public sending: boolean;

	/**
	* These constants are used to limit the message types allowed
	* @const 	object
	*/
	static readonly WUFOO_IDS: any = {
		name: "Field3",
		email: "Field4",
		message: "Field5"
	};

	/**
	* The form builder validator. Handles the form submission of the text-based adventure
	* @param 	JSON object
	*/
	public contactForm = this.formBuilder.group({
		name: ["", Validators.compose([Validators.required, Validators.minLength(1)])], 
		email: ["", Validators.compose([Validators.required, Validators.minLength(1), Validators.email])],
		message: ["", Validators.compose([Validators.required, Validators.minLength(1)])],
		leaveBlank: ["", Validators.maxLength(0)]
	});

	/**
	* Must pass in the data object for the columns to work. 
	* Accepts columns.class, columns.content
	* @param 	ActivatedRoute  	route
	* @param 	ContactService 	contactService
	* @param 	FormBuilder 	formBuilder
	*/
	constructor(private route: ActivatedRoute, private contactService: ContactService, public formBuilder: FormBuilder) { 

		this.router = route;
	}

	/**
	* When view is initialized
	*/
	public ngOnInit() {
	
		this.router.snapshot.params['messages'];
		this.sub = this.router.data.subscribe((v) => { this.subscriber(v) });
		this.contactForm.valueChanges.debounceTime(1000).subscribe((data) => { this.contactFormChanged(data) });
		this.fillContactForm();
	}

	/**
	* Check if any information is in local storage for this form and pre-fill the values
	*/
	public fillContactForm() {

		let d = this.contactService.getContactFormData();
		if (d != null) {
			for (var prop in d) {
				if (d[prop] != null && d[prop].length > 0 && this.contactForm.controls.hasOwnProperty(prop)) 
					this.contactForm.controls[prop].setValue(d[prop]);
			}
		}
	}

	/**
	* Router information observable
	* @param 	Observable 	v
	*/
	public subscriber(v) {

	}

	/**
	* When view is updated
	*/
	public ngOnChanges() {

	}

	/**
	* When view dissapears
	*/
	public ngOnDestroy() {

		this.sub.unsubscribe();
	}

	/**
	* Listen to when the form is updated 
	* @param 	event 	data
	*/
	public contactFormChanged(data) {

		this.contactService.setContactFormData(data);
	}

	/**
	* Fired when the contact form is submitted, will validate, POST to Wufoo and return response
	* @param 	object 	event
	*/
	public contact(event: any) {

		let data = this.contactForm.value;
		if (this.isValid()) {
			let d = this.constructWufooData(data);
			this.setIsSending(true);
			this.contactService.sendEnquiry(d).subscribe(
				(enquiry) => { this.sendEnquirySuccess(enquiry); },
				(error) => { this.sendEnquiryError(error) },
				() => { this.sendEnquiryComplete() }
			);
		} else {

		}
	}

	/**
	* Format the data into Wufoo friendly information
	* @param 	object
	* @return 	JSON
	*/
	public constructWufooData(data: any) {

		let r = {};
		for (var prop in data) {
			if (ContactComponent.WUFOO_IDS.hasOwnProperty(prop)) {
				r[ContactComponent.WUFOO_IDS[prop].toString()] = data[prop];
			}
		}
		return r;
	}

	/**
	* Check the validators for all controls in contact form are valid
	*/
	public isValid() {

		let c = this.contactForm.controls;
		let valid = true;
		for (var prop in c) {
			if (c.hasOwnProperty(prop) && !c[prop].valid) {
				valid = false;
				break;
			}
		}
		return valid;
	}

	/**
	* Enquiry successfully sent to Wufoo
	* @param 	object 	enquiry
	*/
	private sendEnquirySuccess(enquiry) {

		let e = enquiry[enquiry.length - 1];
		if (e) {
			console.log(enquiry);
			this.success = e.content;
		}
	}

	/**
	* Enquiry unsuccessfully sent to Wufoo
	* @param 	object 	error
	*/
	private sendEnquiryError(error) {

		console.error(error);
		this.err = error;
		this.setIsSending(false);
	}

	/**
	* Enquiry observable complete
	* @param 	object 	complete
	*/
	private sendEnquiryComplete() {

		this.setIsSending(false);
		this.contactForm.controls["message"].setValue(" ");
		setTimeout(() => {
			this.success = "";
			this.err = "";
		}, 6000);
	}

	/**
	* If the contact service is sending, notify the user
	* @param 	isSending 	boolean
	*/
	private setIsSending(isSending: boolean) {

		this.sending = isSending;
	}
}
