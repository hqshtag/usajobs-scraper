let init = magicalData.init;
let number = magicalData.userSettings.number;

if (init && number != 42) {
	const flatten = (arr) => {
		return arr.reduce(function(flat, toFlatten) {
			return flat.concat(Array.isArray(toFlatten) ? flatten(toFlatten) : toFlatten);
		}, []);
	};
	class JobLoader {
		constructor(email, key, number, update = false) {
			this.update = update;
			this.urls = this.update ? this.getUrlWithin(number) : this.getUrls(number);
			this.settings = {
				method: 'GET',
				headers: {
					Host: 'data.usajobs.gov',
					'User-Agent': email,
					'Authorization-Key': key
				}
			};
			this.results = [];
			this.requests = [];
		}

		init = () => {
			this.load(this.urls, this.settings);
			return Promise.all(this.requests).then((results) => {
				results.forEach((result) => {
					this.results.push(result);
				});
			});
		};

		load = (urls, settings) => {
			let req;
			urls.map((url) => {
				req = fetch(url, settings).then((res) => res.json());
				this.requests.push(req);
			});
		};

		getResults = () => {
			return this.results;
		};

		getUrls = (numberOfJobs) => {
			let x, r;
			if (numberOfJobs < 500) {
				return [ `https://data.usajobs.gov/api/search?Page=1&ResultsPerPage=${numberOfJobs}` ];
			} else {
				x = numberOfJobs / 500;
				r = numberOfJobs % 500;
			}
			let res = [];
			let page = 1;
			do {
				res.push(`https://data.usajobs.gov/api/search?Page=${page}&ResultsPerPage=500`);
				page++;
			} while (page < 20 && page <= x);
			if (r !== 0) {
				res.push(`https://data.usajobs.gov/api/search?Page=${page}&ResultsPerPage=${r}`);
			}
			return res;
		};
		getUrlWithin = (numberOfDays) => {
			return [ `https://data.usajobs.gov/api/search?DatePosted=${numberOfDays}&ResultsPerPage=500` ];
		};
	}

	class Parser {
		constructor(map) {
			this.readyJobAds = [];
			this.jobAds = [];
			this.typeMap = map; //obj
		}

		init = (results) => {
			this.parseResults(results);
			this.parseJobs(flatten(this.jobAds));
		};

		parseResults = (results) => {
			//parsing the fetch result to an actual job
			results.map((result) => {
				this.jobAds.push(result.SearchResult.SearchResultItems);
			});
		};
		parseJobs = (jobs) => {
			jobs.map((job) => {
				this.readyJobAds.push(this.parseJob(job));
			});
		};

		parseJob = (job) => {
			job = job.MatchedObjectDescriptor;
			let typeCode = job.PositionSchedule[0].Code;
			return {
				status: 'publish',
				type: 'job_listing',
				title: job.PositionTitle,
				'job-types': this.typeMap[typeCode],
				//'job-categories': [ 8 ],
				content: this.getContent(job),
				meta: {
					_job_location: job.PositionLocation[0].LocationName,
					_company_name: job.OrganizationName,
					_job_expires: job.ApplicationCloseDate
				},
				_job_expires: job.ApplicationCloseDate,
				geolocation_lat: job.PositionLocation[0].Latitude,
				geolocation_long: job.PositionLocation[0].Longitude,
				geolocation_formatted_address: job.PositionLocation[0].LocationName,
				geolocation_city: job.PositionLocation[0].CityName,
				geolocated: 1
			};
		};
		getContent = (job) => {
			let summary, qualification, offeringType, salMin, salMax, per, endDate, link, remuneration;
			summary = job.UserArea.Details.JobSummary;
			qualification = job.QualificationSummary;
			offeringType = job.PositionOfferingType[0].Name;
			salMin = job.PositionRemuneration[0].MinimumRange;
			salMax = job.PositionRemuneration[0].MinimumRange ? job.PositionRemuneration[0].MinimumRange : null;
			per = job.PositionRemuneration[0].RateIntervalCode ? job.PositionRemuneration[0].RateIntervalCode : null;
			endDate = job.ApplicationCloseDate;

			link = job.ApplyURI[0];

			remuneration = salMin
				? salMax ? `Starting from $${salMin} up to $${salMax}` : `starting from $${salMin}`
				: 'unkown';
			if (per) {
				remuneration += ` ${per}`;
			}
			return `<div class="job_summary jcard">
				<p> <b>Summary: </b> ${summary}</p>
			</div>  
			</br>
			<div class="job_qualification jcard">
				 <p> <b> Qualification: </b> ${qualification} </p>
			</div> 
			</br>
			<div class="job_additional informations jcard">
			  	<p><b>Appointment type: </b>${offeringType} </p> </br>
	   			<p><b>Salary: </b> ${remuneration} </p> </br> 
				<p><b>Available: </b> until ${endDate} </p>
			</div>
			</br>
			<div class="job_application application">
				<a href="${link}" target="_blank" class="application_button button job-apply-button">Apply for job</a>
			</div>
			`;
		};
	}

	class JobAdder {
		constructor(api, nonce, jobs) {
			this.api = api;
			this.jobs = jobs ? jobs : [];
			this.settings = {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					accept: 'application/json',
					'X-WP-Nonce': nonce
				}
			};
			this.requests = [];
			this.results = [];
		}

		init = () => {
			this.add(this.jobs, this.settings);
			return Promise.all(this.requests).then((results) => {
				results.forEach((result) => {
					this.results.push(result);
				});
			});
		};
		add = (jobs, settings) => {
			let req;
			jobs.map((job) => {
				req = fetch(this.api, {
					...settings,
					body: JSON.stringify(job)
				}).then((res) => res.json());
				this.requests.push(req);
			});
		};
	}

	let localHost = magicalData.localHost;
	let auth = magicalData.usajobsAuth;
	let settings = magicalData.userSettings;

	const loader = new JobLoader(auth.email, auth.key, settings.number, settings.update);
	const parser = new Parser(settings.typeMap);
	const adder = new JobAdder(localHost.api, localHost.nonce);

	loader
		.init()
		.then((res) => {
			//console.log(res);
			parser.init(loader.results);
		})
		.then(() => {
			adder.jobs = parser.readyJobAds;
		})
		.then(() => {
			adder.init();
		});
}

/*parser.init(loader.results);
adder.jobs = parser.readyJobAds;
adder.init();*/
