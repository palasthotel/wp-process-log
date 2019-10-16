'use strict';
;(function($, api){

	const url = new URL(api.ajaxurl);

	const apiFetch = (action, data = {})=>{
		url.search = new URLSearchParams({
			action,
			...data
		});
		return fetch(url).then(res => res.json());
	};

	const apiPost = (action, data = {}) =>{
		return new Promise((resolve, reject)=>{
			$.ajax(url+"?action="+action,{
				method: 'POST',
				data,
				success: resolve,
				error: reject,
			})
		});
	};

	api.fetchProcessList = (page, filter = {})=> {
		return apiPost("processes_list",{page, ...filter});
	};
	api.fetchProcessLogs = (pid) => {
		return apiPost("process_logs",{pid});
	}

})(jQuery, ProcessLogAPI);