import axios from 'axios';
import datatable from '@Core/Services/Datatable';

const list = async (lazyParams) => {
  const response = await datatable.list(route('batches.index'), lazyParams);

  return response.data;
};

const getOne = async (id) => {
  const response = await axios.get(route('batches.show', { id }) + '?print=true');

  return response.data?.data;
};

const del = async (id) => {
  await axios.delete(route('batches.destroy', { id }));
  return true;
};

export default {
  list,
  del,
  getOne,
};
