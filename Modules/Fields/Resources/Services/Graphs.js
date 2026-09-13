import axios from 'axios';
import qs from 'qs';

export const getGraph = async (id, type, filters) => {
  const response = await axios.get(route('graphs.index', { id, type }) + `?filters=${qs.stringify(filters)}`);
  return response.data;
};
