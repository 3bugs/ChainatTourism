package th.ac.dusit.dbizcom.chainattourism.net;

import com.google.gson.annotations.SerializedName;

public class AddRatingResponse extends BaseResponse {

    @SerializedName("average_rate")
    public float averageRate;

}