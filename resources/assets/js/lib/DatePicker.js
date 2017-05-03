class DatePicker {

	constructor(data = null) {

		this.date = (data === null) ?
			new Date() : new Date(data);

		this.displayDate = (data === null) ?
			new Date() : new Date(data);

		this.months = [
			"January", "February", "March",
			"April", "May", "June",
			"July", "August", "September",
			"October", "November", "December"
		];

		this.regex = {
			prettyDate: /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/,
            lessPrettyDate: /^(\d{4})-(\d{1,2})-(\d{1,2})$/
		};

		this.visible = false;
		this.triggered = false;

		this.getDays();
	}

	month() {
		return this.months[this.displayDate.getMonth()];
	}

	calendarDate(){
		return this.month() + ' ' + this.displayDate.getFullYear();
	}

	isLeapYear() {
		let year = this.selectedDate.getFullYear();
		return ((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0);
	}

	isCurrentMonth() {
		return this.date.getFullYear() === this.displayDate.getFullYear()
			&& this.date.getMonth() === this.displayDate.getMonth();
	}

	currentDate(day) {
		return (this.isCurrentMonth() && this.date.getDate() === day) ? " selected" : "";
	}

	getDays() {
		let days = [];

		let currentMonth = new Date(
			this.displayDate.getFullYear(), this.displayDate.getMonth(), 1
		);
		let previousMonth = new Date(
			this.displayDate.getFullYear(), this.displayDate.getMonth() - 1, 1
		);
		let nextMonth = new Date(
			this.displayDate.getFullYear(), this.displayDate.getMonth() + 1, 1
		);

		let previousMonthLength = this.daysInMonth(previousMonth),
			currentMonthLength  = this.daysInMonth(currentMonth);

		let previousMonthTail = 1 - currentMonth.getDay(),
			nextMonthHead = 7 - (currentMonthLength + currentMonth.getDay()) % 7;

		// Start from a negative number to get the dates from the previous month
		for (let day = previousMonthTail; day <= currentMonthLength + nextMonthHead; day++) {
			if (day < 1) {
				days.push({
					className : "otherMonth",
					day       : previousMonthLength + day,
					month     : previousMonth.getMonth(),
					year      : previousMonth.getFullYear(),
				});
			}

			else if (day <= currentMonthLength) {
				days.push({
					className : "currentMonth" + this.currentDate(day),
					day 	  : day,
					month     : currentMonth.getMonth(),
					year      : currentMonth.getFullYear(),
				});
			}

			else {
				days.push({
					className : "otherMonth",
					day       : day % currentMonthLength,
					month     : nextMonth.getMonth(),
					year      : nextMonth.getFullYear(),
				});
			}
		}

		this.days = days;
	}

	daysInMonth(date) {
		return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
	}

	parse(input) {
		let parts;

        if (parts = input.match(this.regex.prettyDate)){
            this.set(parts[3], parts[1] - 1, parts[2]);
            this.sync();

        } else if (parts = input.match(this.regex.lessPrettyDate)){
            this.set(parts[1], parts[2] - 1, parts[3]);
            this.sync();
        }
	}

	// 12/25/2000
	pretty() {
		if (!this.triggered) { return ''; }

		return this.date.getMonth() + 1
			+ '/' + this.date.getDate()
			+ '/' + this.date.getFullYear();
	}

	// 2000-12-25
	lessPretty() {
		if (!this.triggered) { return ''; }

		return this.date.getFullYear() + '-'
			+ (this.date.getMonth() + 1) + '-'
			+ this.date.getDate();
	}

	set(year, month, day) {
		this.date = new Date(year, month, day);
		this.displayDate = new Date(year, month, day);
		this.getDays();

		this.trigger();
	}

	show() {
		this.getDays();
		this.visible = true;
	}

	hide() {
		this.visible = false;
	}

	sync() {
		this.displayDate = new Date(
			this.date.getFullYear(),
			this.date.getMonth(),
			this.date.getDate()
		);
	}

	trigger(){
		this.triggered = true;
	}

	displayPreviousMonth() {
		this.displayDate.setMonth(this.displayDate.getMonth() - 1);
		this.getDays();
	}

	displayNextMonth() {
		this.displayDate.setMonth(this.displayDate.getMonth() + 1);
		this.getDays();
	}

	setDay(date) {
		this.date.setDate(date);
		this.getDays();
	}
}

export default DatePicker;
