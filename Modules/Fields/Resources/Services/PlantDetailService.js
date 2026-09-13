import axios from 'axios';

const list = async (route, show_harvests) => {
  const response = await axios.get(route, {
    params: {
      show_harvests,
    },
  });
  return response.data.data;
};

const store = async (form) => {
  try {
    const options = {};
    const formData = form.data();
    if ([formData.foliage_sanitation_photo, formData.trunk_sanitation_photo, formData.soil_sanitation_photo].some((d) => d != null)) {
      options.headers = {
        'Content-Type': 'multipart/form-data',
      };
    }

    await axios.post(route('plants.details.store'), formData, options);
    return true;
  } catch (error) {
    return false;
  }
};

const listByPlantId = async (id, year, show_harvests) => {
  return await list(route('plants.details.index', { id, year }), show_harvests);
};

const listByQuarterId = async (id, year, show_harvests) => {
  return await list(route('plants.details.by_quarter', { id, year }), show_harvests);
};

const listByFieldId = async (id, year, show_harvests) => {
  return await list(route('plants.details.by_field', { id, year }), show_harvests);
};

export default {
  store,
  listByPlantId,
  listByQuarterId,
  listByFieldId,
};
