package th.ac.dusit.dbizcom.chainattourism.net;

import com.google.gson.annotations.SerializedName;

import java.util.List;

import th.ac.dusit.dbizcom.chainattourism.model.Otop;
import th.ac.dusit.dbizcom.chainattourism.model.Place;

public class GetOtopResponse extends BaseResponse {

    @SerializedName("data_list")
    public List<Otop> otopList;

}