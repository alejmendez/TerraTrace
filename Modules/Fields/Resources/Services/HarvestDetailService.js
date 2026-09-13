import axios from 'axios';

const store = async (form) => {
  try {
    const formData = form.data();
    await axios.post(route('harvests_details.store'), formData);
    return true;
  } catch (error) {
    return false;
  }
};

export default {
  store,
};
