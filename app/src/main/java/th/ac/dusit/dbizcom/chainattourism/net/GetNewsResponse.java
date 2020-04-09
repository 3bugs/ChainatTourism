package th.ac.dusit.dbizcom.chainattourism.net;

import com.google.gson.annotations.SerializedName;

import java.util.List;

import th.ac.dusit.dbizcom.chainattourism.model.News;

public class GetNewsResponse extends BaseResponse {

    @SerializedName("data_list")
    public List<News> newsList;

}