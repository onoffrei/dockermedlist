var vanillaCalendar = {
  init: function (options) {
    this.options = options
    this.date.setDate(1)
    this.createMonth()
    this.createListeners()
  },

  createListeners: function () {
    var _this = this
    this.next.addEventListener('click', function () {
      _this.clearCalendar()
      var nextMonth = _this.date.getMonth() + 1
      _this.date.setMonth(nextMonth)
      _this.createMonth()
		if (typeof(_this.options.onmonthchange) == 'function'){
			_this.options.onmonthchange(_this.date)
		}
    })
    // Clears the calendar and shows the previous month
    this.previous.addEventListener('click', function () {
      _this.clearCalendar()
      var prevMonth = _this.date.getMonth() - 1
      _this.date.setMonth(prevMonth)
      _this.createMonth()
		if (typeof(_this.options.onmonthchange) == 'function'){
			_this.options.onmonthchange(_this.date)
		}
    })
  },

  createDay: function (num, day, year) {
    var newDay = document.createElement('div')
    var dateEl = document.createElement('span')
    dateEl.innerHTML = num
    newDay.className = 'vcal-date'
    newDay.setAttribute('data-calendar-date', this.date)

    // if it's the first day of the month
    if (num === 1) {
      if (day === 0) {
        newDay.style.marginLeft = (6 * 14.28) + '%'
      } else {
        newDay.style.marginLeft = ((day - 1) * 14.28) + '%'
      }
    }

    if (this.options.disablePastDays && this.date.getTime() <= this.todaysDate.getTime() - 1) {
      newDay.classList.add('vcal-date--disabled')
    } else {
      newDay.classList.add('vcal-date--active')
      newDay.setAttribute('data-calendar-status', 'active')
    }

    if (this.date.toString() === this.todaysDate.toString()) {
      newDay.classList.add('vcal-date--today')
    }
	//console.log(this.date,this.selectdate)
    if (this.date.toString() === this.selectdate.toString()) {
      newDay.classList.add('vcal-date--selected')
	  this.selectdate = '';
    }

    newDay.appendChild(dateEl)
    this.month.appendChild(newDay)
  },

  dateClicked: function () {
	vcal_date_click_func = function(event) {
        var picked = document.querySelectorAll(
          '#v-cal [data-calendar-label="picked"]'
        )[0]
        picked.innerHTML = this.dataset.calendarDate
        _this.removeActiveClass()
        this.classList.add('vcal-date--selected')
		if (typeof(_this.options.onclick) == 'function'){
			_this.options.onclick(new Date(picked.innerHTML))
		}
	}
    var _this = this
    this.activeDates = document.querySelectorAll(
      '#v-cal [data-calendar-status="active"]'
    )
    for (var i = 0; i < this.activeDates.length; i++) {
      this.activeDates[i].addEventListener('click', vcal_date_click_func )
    }
  },

  createMonth: function () {
    var currentMonth = this.date.getMonth()
    while (this.date.getMonth() === currentMonth) {
      this.createDay(
        this.date.getDate(),
        this.date.getDay(),
        this.date.getFullYear()
      )
      this.date.setDate(this.date.getDate() + 1)
    }
    // while loop trips over and day is at 30/31, bring it back
    this.date.setDate(1)
    this.date.setMonth(this.date.getMonth() - 1)

    this.label.innerHTML =
      this.monthsAsString(this.date.getMonth()) + ' ' + this.date.getFullYear()
    this.dateClicked()
  },

  monthsAsString: function (monthIndex) {
    return [
      'Ianuarie',
      'Februarie',
      'Martie',
      'Aprilie',
      'Mai',
      'Iunie',
      'Iulie',
      'August',
      'Septembrie',
      'Octombrie',
      'Noiembrie',
      'Decembrie'
    ][monthIndex]
  },

  clearCalendar: function () {
    vanillaCalendar.month.innerHTML = ''
  },

  removeActiveClass: function () {
    for (var i = 0; i < this.activeDates.length; i++) {
      this.activeDates[i].classList.remove('vcal-date--selected')
    }
  },
  
  redraw: function(options){
		var currentTime = new Date()
		var currentTimestr = currentTime.getFullYear() 
			+ '-' + (currentTime.getMonth() + 1)
			+ '-' + currentTime.getDate()
		if (typeof(options) == 'undefined') options = {}
		if (typeof(options.elid) == 'undefined') options.elid = 'v-cal'
		if (typeof(options.elidhidden) == 'undefined') options.elidhidden = 'v-cal-hidden'
		if (typeof(options.disablePastDays) == 'undefined') options.disablePastDays = true
		if (typeof(options.initdate) == 'undefined') options.initdate = sqlToJsDate(currentTimestr)
		if (typeof(options.selectdate) == 'undefined') options.selectdate = sqlToJsDate(currentTimestr)
		$('#' + options.elid).html($('#' + options.elidhidden).html())
		this.month = document.querySelectorAll('#v-cal [data-calendar-area="month"]')[0]
		this.next = document.querySelectorAll('#v-cal [data-calendar-toggle="next"]')[0]
		this.previous = document.querySelectorAll('#v-cal [data-calendar-toggle="previous"]')[0]
		this.label = document.querySelectorAll('#v-cal [data-calendar-label="month"]')[0]
		this.activeDates = null
		this.date = options.initdate
		this.selectdate = options.selectdate
		this.todaysDate = sqlToJsDate(currentTimestr)
		this.init(options);		  
  }
}