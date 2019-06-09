package th.ac.dusit.dbizcom.chainattourism.net;

import retrofit2.Call;
import retrofit2.http.GET;
import retrofit2.http.Query;
import th.ac.dusit.dbizcom.chainattourism.model.Place;

public interface WebServices {

    @GET("get_place")
    Call<GetPlaceResponse> getPlace(
            @Query("place_type") Place.PlaceType placeType
    );

}