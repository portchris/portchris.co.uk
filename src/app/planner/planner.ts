export class Planner {

	// public monday: any;
	// public tuesday: any;
	// public wednesday: any;
	// public thursday: any;
	// public friday: any;
	// public saturday: any;
	// public sunday: string;
	public today: string;
	public resourcePlanner: any;
	public success: boolean;
	private _days: Array<any> = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

	public constructor(plans: any) {

		// this.monday = data.monday;
		// this.tuesday = data.tuesday;
		// this.wednesday = data.wednesday;
		// this.thursday = data.thursday;
		// this.friday = data.friday;
		// this.saturday = data.saturday;
		// this.sunday = data.sunday;

		if (plans.data != null && plans.success) {
			for (let i in this._days) {
				if (this._days[i] in plans.data && plans.data[this._days[i]] != null) {
					this.resourcePlanner[this._days[i]] = {
						name: this._days[i],
						active: (this.today === this._days[i]) ? true : false,
						plans: plans.data[this._days[i]]
					};
				}
			}
			this.success = true;
		}
	}
}