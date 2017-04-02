import Queue from '../lib/Queue.js';

class Hashtags extends Queue {

    constructor(data) {
        super();

        if (Array.isArray(data)) {
            data.forEach(function(hashtag) {
                this.add(hashtag);
            }.bind(this));
        }
    }

    add(data) {
        let cleanData = data.replace(/[\s|,]/g, '');

        if (cleanData != '') {
            return super.add(cleanData);
        }
    }
}

export default Hashtags;