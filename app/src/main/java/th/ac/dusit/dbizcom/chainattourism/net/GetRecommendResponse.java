package th.ac.dusit.dbizcom.chainattourism.net;

import com.google.gson.annotations.SerializedName;

import java.util.List;

import th.ac.dusit.dbizcom.chainattourism.model.Place;

public class GetRecommendResponse extends BaseResponse {

    @SerializedName("place_list")
    public List<Place> placeList;
    @SerializedName("temple_list")
    public List<Place> templeList;
    @SerializedName("restaurant_list")
    public List<Place> restaurantList;
    @SerializedName("hotel_list")
    public List<Place> hotelList;
    @SerializedName("otop_list")
    public List<Place> otopList;

}