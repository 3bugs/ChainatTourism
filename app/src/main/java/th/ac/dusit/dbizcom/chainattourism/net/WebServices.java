package th.ac.dusit.dbizcom.chainattourism.net;

import retrofit2.Call;
import retrofit2.http.Field;
import retrofit2.http.FormUrlEncoded;
import retrofit2.http.GET;
import retrofit2.http.POST;
import retrofit2.http.Query;
import th.ac.dusit.dbizcom.chainattourism.model.Place;

public interface WebServices {

    @GET("get_place")
    Call<GetPlaceResponse> getPlace(
            @Query("place_type") Place.PlaceType placeType
    );

    @GET("get_recommend")
    Call<GetRecommendResponse> getRecommend(
    );

    @GET("get_otop_by_district")
    Call<GetOtopResponse> getOtopByDistrict(
            @Query("district") String districtName
    );

    @FormUrlEncoded
    @POST("add_rating")
    Call<AddRatingResponse> addRating(
            @Field("id") int itemId,
            //@Field("type") String itemType,
            @Field("rate") int rate
    );

}