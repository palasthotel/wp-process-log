'use strict';
;(function(api){

	/**
	 * POSTs form-encoded to admin-ajax.php, the way the handlers read $_REQUEST.
	 * @param {string} action
	 * @param {object} data
	 * @return {Promise<object>}
	 */
	const apiPost = (action, data = {}) =>{
		return fetch(api.ajaxurl, {
			method: 'POST',
			credentials: 'same-origin',
			body: new URLSearchParams({ action, _ajax_nonce: api.nonce, ...data }),
		}).then(res => {
			if (!res.ok) {
				throw new Error(`${action} failed with status ${res.status}`);
			}
			return res.json();
		});
	};

	api.fetchProcessList = (page, filter = {})=> {
		return apiPost("processes_list",{page, ...filter});
	};
	api.fetchProcessLogs = (pid) => {
		return apiPost("process_logs",{pid});
	}

})(ProcessLogAPI);
