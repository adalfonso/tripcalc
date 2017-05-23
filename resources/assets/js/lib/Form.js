import Errors from './Errors.js'

class Form {

    constructor(data) {
        this.originalData = data;

        for (let field in data) {
            this[field] = data[field];
        }

        this.errors = new Errors();
    }

    data(request = null) {
        let data = {};

        for (let property in this.originalData) {
            data[property] = this[property];
        }

        return request === 'delete'
            ? { data : data }
            :   data;
    }

    isDeletable() {
        return this.deletable === true;
    }

    get (url) {
        return this.submit('get', url);
    }

    post (url) {
         return this.submit('post', url);
    }

    put (url) {
        return this.submit('put', url);
    }

    patch (url) {
        return this.submit('patch', url);
    }

    delete (url) {
        return this.submit('delete', url);
    }

    reset() {
        for (let field in this.originalData) {
            this[field] = '';
        }

        this.errors.clear();
    }

    setPasswordNull() {
        this.password = null;
        this.delete_confirmation = false;
    }

    submit(requestType, url) {
        return new Promise((resolve, reject) => {
            axios[requestType](url, this.data(requestType))
                .then(response => {
                    this.onSuccess(response.data);
                    resolve(response.data);
                })
                .catch(error => {
                    this.onFail(error.response.data);
                    reject(error.response.data);
                });
        });
    }

    toggle(property) {
        this[property] = !this[property];

        if (property === 'delete') {
            this.setPasswordNull();
        }
    }

    onSuccess(data) {
        //this.reset();
    }

    onFail(errors) {
        this.errors.record(errors);
    }
}

export default Form;
