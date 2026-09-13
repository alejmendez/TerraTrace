import axios from 'axios';

const list = async (params, signal) => {
  const response = await axios.get(route('quarters.index'), {
    params: { collection: 1, ...params },
    signal,
  });

  return response.data;
};

const del = async (id) => {
  await axios.delete(route('quarters.destroy', { id }));
  return true;
};

const getPlants = async (id) => {
  const response = await axios.get(route('quarters.plants', { id }));

  return response.data;
};

const updatePlantsPosition = async (id, data) => {
  const response = await axios.put(route('quarters.plants.update.position', { id }), {
    data,
  });

  return response.data;
};

export default {
  list,
  del,
  getPlants,
  updatePlantsPosition,
};
